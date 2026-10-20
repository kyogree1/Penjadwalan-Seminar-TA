<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class R4SchedulingTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $type = 'sempro'): array
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $supervisor = User::factory()->create(['role' => 'dosen']);
        $a = User::factory()->create(['role' => 'dosen']);
        $b = User::factory()->create(['role' => 'dosen']);
        PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'AI', 'bidang_penelitian' => 'AI', 'status' => 'disetujui', 'pembimbing_1_id' => $supervisor->id]);
        $class = $type === 'sempro' ? PendaftaranSempro::class : PendaftaranSidang::class;
        $reg = $class::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'AI', 'bentuk_ta' => 'Skripsi', 'gelombang' => 1, 'status' => 'verifikasi_tendik', 'status_akademik' => 'disetujui']);
        $slot = ['tanggal' => '2026-11-01', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'];
        $period = SchedulePeriod::create(['name' => 'Wave one', 'tipe' => $type, 'gelombang' => 1, 'capacity' => 10, 'start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'slots' => [$slot], 'is_open' => true]);
        $row = array_merge($slot, ['tipe' => $type, 'pendaftaran_id' => $reg->id, 'mahasiswa_id' => $student->id, 'penguji_1_id' => $a->id, 'penguji_2_id' => $b->id]);
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));

        return [$period, $reg, $row, $supervisor];
    }

    public function test_generate_contract_and_readiness_for_both_types(): void
    {
        foreach (['sempro', 'sidang'] as $type) {
            [$period, $reg, $row, $supervisor] = $this->fixture($type);
            mt_srand(44);
            $result = $this->postJson('/kaprodi/penjadwalan/generate-ga', ['period_id' => $period->id, 'popSize' => 10, 'maxGenerations' => 10])->assertOk()->json();
            $this->assertSame('complete', $result['outcome']);
            $this->assertSame($reg->id, $result['schedule'][0]['pendaftaran_id']);
            $this->assertSame($reg->mahasiswa_id, $result['schedule'][0]['mahasiswa_id']);
            $this->assertSame($type, $result['schedule'][0]['tipe']);
            $this->assertContains($supervisor->id, $result['schedule'][0]['supervisor_ids']);
            $reg->update(['status_akademik' => 'revisi']);
            $this->postJson('/kaprodi/penjadwalan/generate-ga', ['period_id' => $period->id])->assertJsonPath('outcome', 'infeasible');
        }
    }

    public function test_generation_batches_existing_schedule_lookup(): void
    {
        [$period] = $this->fixture();
        for ($i = 0; $i < 4; $i++) {
            $this->fixture();
        }
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            if (str_contains($query->sql, 'penjadwalan')) {
                $queries[] = $query->sql;
            }
        });
        $this->postJson('/kaprodi/penjadwalan/generate-ga', ['period_id' => $period->id, 'popSize' => 10, 'maxGenerations' => 10])->assertOk();
        $this->assertCount(3, $queries, implode("\n", $queries));
    }

    public function test_publish_is_idempotent_and_reload_is_period_isolated(): void
    {
        [$period, $reg, $row] = $this->fixture();
        $payload = ['period_id' => $period->id, 'schedules' => [$row]];
        $this->post('/kaprodi/penjadwalan/simpan', $payload)->assertSessionHas('success');
        $this->post('/kaprodi/penjadwalan/simpan', $payload)->assertSessionHas('success');
        $this->assertDatabaseCount('penjadwalan', 1);
        $this->assertSame('terjadwal', $reg->fresh()->status);
        $this->assertSame($period->id, Penjadwalan::first()->period_id);
        $this->get('/kaprodi/penjadwalan/plotting-sempro?period_id='.$period->id)->assertInertia(fn ($page) => $page->has('schedules', 1)->where('schedules.0.mahasiswa_id', $reg->mahasiswa_id));
        $other = $period->replicate();
        $other->name = 'Other';
        $other->save();
        $this->get('/kaprodi/penjadwalan/plotting-sempro?period_id='.$other->id)->assertInertia(fn ($page) => $page->has('schedules', 0));
    }

    public function test_supervisor_and_existing_interval_conflicts_are_rejected(): void
    {
        [$period, $reg, $row, $supervisor] = $this->fixture();
        $bad = array_merge($row, ['penguji_1_id' => $supervisor->id]);
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$bad]])->assertSessionHasErrors('schedules');
        $booked = array_merge($row, ['pendaftaran_id' => 999, 'mahasiswa_id' => User::factory()->create(['role' => 'mahasiswa'])->id, 'jam_mulai' => '08:30:00', 'jam_selesai' => '09:30:00', 'status' => 'terpublikasi']);
        Penjadwalan::create($booked);
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHasErrors('schedules');
        $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
    }

    public function test_batch_failure_rolls_back_first_record(): void
    {
        [$period, $reg, $row] = $this->fixture();
        $bad = array_merge($row, ['pendaftaran_id' => 999]);
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row, $bad]])->assertSessionHasErrors('schedules');
        $this->assertDatabaseCount('penjadwalan', 0);
        $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
    }

    public function test_database_failure_after_first_save_rolls_back_all_writes(): void
    {
        [$period, $reg, $row] = $this->fixture();
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $supervisor = User::factory()->create(['role' => 'dosen']);
        PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Second', 'bidang_penelitian' => 'AI', 'status' => 'disetujui', 'pembimbing_1_id' => $supervisor->id]);
        $second = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Second', 'bentuk_ta' => 'Skripsi', 'gelombang' => 1, 'status' => 'verifikasi_tendik', 'status_akademik' => 'disetujui']);
        $slot = ['tanggal' => '2026-11-02', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'];
        $period->update(['slots' => array_merge($period->slots, [$slot])]);
        $next = array_merge($row, $slot, ['pendaftaran_id' => $second->id, 'mahasiswa_id' => $student->id]);
        $writes = 0;
        Penjadwalan::creating(function () use (&$writes) {
            if (++$writes === 2) {
                throw new \RuntimeException('Injected persistence failure');
            }
        });
        $this->withoutExceptionHandling();
        try {
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row, $next]]);
            $this->fail('Expected persistence failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Injected persistence failure', $exception->getMessage());
        } finally {
            Penjadwalan::flushEventListeners();
        }
        $this->assertDatabaseCount('penjadwalan', 0);
        $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
        $this->assertSame('verifikasi_tendik', $second->fresh()->status);
    }

    public function test_override_revalidates_and_does_not_change_registration_identity(): void
    {
        [$period, $reg, $row, $supervisor] = $this->fixture();
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
        $schedule = Penjadwalan::first();
        $this->patch('/kaprodi/penjadwalan/'.$schedule->id, ['penguji_1_id' => $supervisor->id])->assertSessionHasErrors('schedules');
        $this->assertSame($row['penguji_1_id'], $schedule->fresh()->penguji_1_id);
    }

    public function test_manual_examiner_override_persists_for_both_types(): void
    {
        foreach (['sempro', 'sidang'] as $type) {
            [$period, $reg, $row, $supervisor] = $this->fixture($type);
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
            $schedule = Penjadwalan::where('period_id', $period->id)->sole();
            $replacement = User::factory()->create(['role' => 'dosen']);
            foreach (['penguji_1_id', 'penguji_2_id'] as $field) {
                $expected = $schedule->fresh()->only(['penguji_1_id', 'penguji_2_id']);
                $expected[$field] = $replacement->id;
                $this->patch('/kaprodi/penjadwalan/'.$schedule->id, [$field => $replacement->id, 'pendaftaran_id' => 999, 'mahasiswa_id' => 999, 'tipe' => 'invalid', 'period_id' => 999])
                    ->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
                $this->assertDatabaseHas('penjadwalan', array_merge($expected, ['id' => $schedule->id, 'period_id' => $period->id, 'pendaftaran_id' => $reg->id, 'mahasiswa_id' => $reg->mahasiswa_id, 'tipe' => $type, 'status' => 'terpublikasi', 'pembimbing_1_id' => $supervisor->id, 'tanggal' => $row['tanggal'].' 00:00:00', 'jam_mulai' => $row['jam_mulai'], 'jam_selesai' => $row['jam_selesai']]));
                $this->assertSame('terjadwal', $reg->fresh()->status);
                $replacement = User::factory()->create(['role' => 'dosen']);
            }
            $this->assertSame(1, Penjadwalan::where('period_id', $period->id)->count());
            $this->get('/kaprodi/penjadwalan/plotting-' . ($type === 'sempro' ? 'sempro' : 'ta') . '?period_id='.$period->id)->assertInertia(fn ($page) => $page->has('schedules', 1)->where('schedules.0.penguji1_id', $expected['penguji_1_id'])->where('schedules.0.penguji2_id', $expected['penguji_2_id']));
        }
    }

    public function test_manual_time_override_persists_for_both_types(): void
    {
        foreach (['sempro', 'sidang'] as $type) {
            [$period, $reg, $row] = $this->fixture($type);
            $slot = ['tanggal' => '2026-11-02', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'];
            $period->update(['slots' => array_merge($period->slots, [$slot])]);
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
            $schedule = Penjadwalan::where('period_id', $period->id)->sole();
            $this->patch('/kaprodi/penjadwalan/'.$schedule->id, $slot)->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
            $this->assertDatabaseHas('penjadwalan', array_merge($schedule->only(['id', 'period_id', 'pendaftaran_id', 'mahasiswa_id', 'tipe', 'penguji_1_id', 'penguji_2_id', 'pembimbing_1_id', 'pembimbing_2_id', 'status']), $slot, ['tanggal' => $slot['tanggal'].' 00:00:00']));
            $this->assertSame('terjadwal', $reg->fresh()->status);
            $this->assertSame(1, Penjadwalan::where('period_id', $period->id)->count());
            $this->get('/kaprodi/penjadwalan/plotting-' . ($type === 'sempro' ? 'sempro' : 'ta') . '?period_id='.$period->id)->assertInertia(fn ($page) => $page->has('schedules', 1)->where('schedules.0.mulai', substr($slot['jam_mulai'], 0, 5))->where('schedules.0.selesai', substr($slot['jam_selesai'], 0, 5)));
        }
    }

    public static function invalidGaParameters(): array
    {
        $cases = [];
        foreach (['period_id' => [null, '', 'invalid', [], ['id' => 1], true, false, 1.5, '1.5', 0, -1, 999999], 'popSize' => ['invalid', [], ['value' => 10], true, false, 10.5, '10.5', 9, 201], 'maxGenerations' => ['invalid', [], ['value' => 10], true, false, 10.5, '10.5', 9, 501], 'crossoverRate' => ['invalid', [], ['value' => 0.5], true, false, -0.001, 1.001], 'mutationRate' => ['invalid', [], ['value' => 0.5], true, false, -0.001, 1.001]] as $field => $values) {
            foreach ($values as $index => $value) {
                $cases[$field.' invalid '.$index] = [$field, $value];
            }
        }

        return $cases;
    }

    #[DataProvider('invalidGaParameters')]
    public function test_invalid_ga_parameters_are_rejected_without_writes(string $field, mixed $value): void
    {
        [$period, $reg] = $this->fixture();
        $payload = ['period_id' => $period->id, 'popSize' => 10, 'maxGenerations' => 10, 'crossoverRate' => 0.8, 'mutationRate' => 0.03];
        $payload[$field] = $value;
        $this->postJson('/kaprodi/penjadwalan/generate-ga', $payload)->assertUnprocessable()->assertJsonValidationErrors([$field])->assertJsonMissingPath('schedule');
        $this->assertDatabaseCount('penjadwalan', 0);
        $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
        $this->assertTrue($period->fresh()->is_open);
    }

    public function test_ga_requires_an_existing_open_period(): void
    {
        [$period, $reg] = $this->fixture();
        $this->postJson('/kaprodi/penjadwalan/generate-ga', [])->assertUnprocessable()->assertJsonValidationErrors('period_id');
        $period->update(['is_open' => false]);
        $this->postJson('/kaprodi/penjadwalan/generate-ga', ['period_id' => $period->id])->assertUnprocessable()->assertJsonValidationErrors('period_id');
        $this->assertDatabaseCount('penjadwalan', 0);
        $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
    }

    public function test_ga_accepts_parameter_endpoints_and_nullable_defaults(): void
    {
        [$period, $reg] = $this->fixture();
        // No candidates keeps the maximum-boundary check bounded and deterministic.
        $reg->update(['status_akademik' => 'revisi']);
        foreach ([['popSize' => 10, 'maxGenerations' => 10, 'crossoverRate' => 0, 'mutationRate' => 0], ['popSize' => 200, 'maxGenerations' => 500, 'crossoverRate' => 1, 'mutationRate' => 1], ['popSize' => null, 'maxGenerations' => null, 'crossoverRate' => null, 'mutationRate' => null], []] as $parameters) {
            $this->postJson('/kaprodi/penjadwalan/generate-ga', array_merge(['period_id' => $period->id], $parameters))->assertOk()->assertJsonPath('period_id', $period->id)->assertJsonPath('schedule', []);
        }
        $this->assertDatabaseCount('penjadwalan', 0);
    }

    public function test_published_period_can_close_without_changing_schedule_definitions(): void
    {
        [$period, $reg, $row] = $this->fixture();
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
        $schedule = Penjadwalan::sole()->getAttributes();
        $definition = $period->getAttributes();
        $data = array_merge($period->only(['name', 'tipe', 'gelombang', 'capacity', 'slots']), ['start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'is_open' => false]);
        $this->patch('/kaprodi/penjadwalan/periods/'.$period->id, $data)->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertFalse($period->fresh()->is_open);
        $this->assertSame($schedule, Penjadwalan::sole()->getAttributes());
        foreach (['name', 'tipe', 'gelombang', 'capacity', 'start_date', 'end_date'] as $field) {
            $this->assertSame($definition[$field], $period->fresh()->getAttributes()[$field]);
        }
        $this->assertSame($period->slots, $period->fresh()->slots);
        $this->assertSame('terjadwal', $reg->fresh()->status);
        $this->postJson('/kaprodi/penjadwalan/generate-ga', ['period_id' => $period->id])->assertUnprocessable()->assertJsonValidationErrors('period_id');
        $this->postJson('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertUnprocessable()->assertJsonValidationErrors('schedules');
    }

    public function test_reopening_published_period_rejects_another_open_wave_of_same_type(): void
    {
        [$period, , $row] = $this->fixture();
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
        $period->update(['is_open' => false]);
        $other = $period->replicate();
        $other->fill(['name' => 'Wave two', 'gelombang' => 2, 'is_open' => true])->save();
        $data = array_merge($period->only(['name', 'tipe', 'gelombang', 'capacity', 'slots']), ['start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'is_open' => true]);
        $this->patch('/kaprodi/penjadwalan/periods/'.$period->id, $data)->assertSessionHasErrors('is_open');
        $this->assertFalse($period->fresh()->is_open);
        $this->assertTrue($other->fresh()->is_open);
        $other->update(['is_open' => false]);
        $this->patch('/kaprodi/penjadwalan/periods/'.$period->id, $data)->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertTrue($period->fresh()->is_open);
    }

    public function test_published_period_rejects_structural_edits_even_when_closing(): void
    {
        [$period, , $row] = $this->fixture();
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
        $before = $period->fresh()->getAttributes();
        $schedule = Penjadwalan::sole()->getAttributes();
        $data = array_merge($period->only(['name', 'tipe', 'gelombang', 'capacity', 'slots']), ['start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'is_open' => false]);
        foreach (['name' => 'Changed', 'tipe' => 'sidang', 'gelombang' => 2, 'capacity' => 11, 'start_date' => '2026-10-31', 'end_date' => '2026-11-03', 'slots' => [array_merge($row, ['jam_mulai' => '09:30:00'])]] as $field => $value) {
            $this->patch('/kaprodi/penjadwalan/periods/'.$period->id, array_merge($data, [$field => $value]))->assertSessionHasErrors('slots');
            $this->assertSame($before, $period->fresh()->getAttributes());
            $this->assertSame($schedule, Penjadwalan::sole()->getAttributes());
        }
    }

    public function test_cancelled_schedule_reactivation_requires_free_capacity(): void
    {
        foreach (['sempro', 'sidang'] as $type) {
            [$period, $reg, $row] = $this->fixture($type);
            $period->update(['capacity' => 1]);
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHas('success');
            $cancelled = Penjadwalan::where('period_id', $period->id)->sole();
            $cancelled->update(['status' => 'dibatalkan']);
            $reg->refresh()->update(['status' => 'verifikasi_tendik']);
            [, $secondReg, $secondRow] = $this->fixture($type);
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$secondRow]])->assertSessionHas('success');
            $before = $cancelled->fresh()->getAttributes();
            $this->postJson('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertUnprocessable()->assertJsonValidationErrors('schedules')->assertJsonPath('errors.schedules.0', 'Kapasitas periode habis.');
            $this->assertSame($before, $cancelled->fresh()->getAttributes());
            $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
            $this->assertSame(1, Penjadwalan::where('period_id', $period->id)->where('status', '!=', 'dibatalkan')->count());
            // An already-active row does not consume another capacity unit.
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$secondRow]])->assertSessionHasNoErrors()->assertSessionHas('success');
            Penjadwalan::where('period_id', $period->id)->where('pendaftaran_id', $secondReg->id)->update(['status' => 'dibatalkan']);
            $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => $period->id, 'schedules' => [$row]])->assertSessionHasNoErrors()->assertSessionHas('success');
            $this->assertSame('terpublikasi', $cancelled->fresh()->status);
            $this->assertSame('terjadwal', $reg->fresh()->status);
            $this->assertSame(2, Penjadwalan::where('period_id', $period->id)->count());
        }
    }

    public function test_publication_rejects_boolean_period_id_without_writes(): void
    {
        [$period, $reg, $row] = $this->fixture();
        $this->assertGreaterThan(0, $period->id);
        foreach ([true, false] as $value) {
            $this->postJson('/kaprodi/penjadwalan/simpan', ['period_id' => $value, 'schedules' => [$row]])->assertUnprocessable()->assertJsonValidationErrors('period_id');
            $this->assertDatabaseCount('penjadwalan', 0);
            $this->assertSame('verifikasi_tendik', $reg->fresh()->status);
        }
        $this->post('/kaprodi/penjadwalan/simpan', ['period_id' => (string) $period->id, 'schedules' => [$row]])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertDatabaseCount('penjadwalan', 1);
    }

    public function test_period_actions_persist_real_dates_and_reject_outside_slots(): void
    {
        [$period] = $this->fixture();
        $data = $period->only(['name', 'tipe', 'gelombang', 'capacity', 'start_date', 'end_date', 'slots', 'is_open']);
        $data['name'] = 'Persisted period';
        $data['is_open'] = false;
        $data['start_date'] = '2026-11-01';
        $data['end_date'] = '2026-11-02';
        $this->post('/kaprodi/penjadwalan/periods', $data)->assertSessionHas('success');
        $this->assertDatabaseHas('schedule_periods', ['name' => 'Persisted period']);
        $this->patch('/kaprodi/penjadwalan/periods/'.$period->id, array_merge($data, ['is_open' => false]))->assertSessionHas('success');
        $this->assertFalse($period->fresh()->is_open);
        $data['slots'][0]['tanggal'] = '2026-12-01';
        $this->post('/kaprodi/penjadwalan/periods', $data)->assertSessionHasErrors('slots');
    }
}

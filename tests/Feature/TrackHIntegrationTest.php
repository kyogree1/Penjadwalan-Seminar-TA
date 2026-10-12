<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TrackHIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_lecturer_creation_uses_random_secret_without_disclosure(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));
        Str::createRandomStringsUsing(fn () => 'unique-generated-lecturer-secret');
        try {
            $this->post('/kaprodi/dosen', ['name' => 'Secure lecturer', 'email' => 'secure@example.test', 'nip' => '000987', 'prodi' => 'Informatika'])->assertSessionHas('success');
            $user = User::where('email', 'secure@example.test')->firstOrFail();
            $this->assertTrue(Hash::check('unique-generated-lecturer-secret', $user->password));
            $this->assertFalse(Hash::check('password123', $user->password));
            $this->assertStringNotContainsString('unique-generated-lecturer-secret', session('success'));
        } finally {
            Str::createRandomStringsNormally();
        }
    }

    public function test_registration_matches_open_period_wave_and_rejects_missing_or_ambiguous_periods(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']));
        foreach (['sempro' => PendaftaranSempro::class, 'sidang' => PendaftaranSidang::class] as $type => $model) {
            $data = ['judul_ta' => 'Actual title', 'bentuk_ta' => 'Skripsi'];
            $this->post('/pendaftaran/'.$type, $data)->assertSessionHasErrors('gelombang');
            $period = SchedulePeriod::create(['name' => 'Open period', 'tipe' => $type, 'gelombang' => 7, 'capacity' => 10, 'start_date' => '2026-11-01', 'end_date' => '2026-11-02', 'slots' => [], 'is_open' => true]);
            $this->post('/pendaftaran/'.$type, $data)->assertSessionHas('success');
            $this->assertEquals(7, $model::firstOrFail()->gelombang);
            $other = $period->replicate();
            $other->gelombang = 8;
            $other->save();
            $this->post('/pendaftaran/'.$type, $data)->assertSessionHasErrors('gelombang');
            $this->assertEquals(7, $model::firstOrFail()->gelombang);
        }
    }

    public function test_sempro_revision_retains_files_and_closed_wave_but_resets_review(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $row = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Original', 'bentuk_ta' => 'Skripsi', 'status' => 'verifikasi_tendik', 'status_akademik' => 'revisi', 'catatan_kaprodi' => 'Correct title', 'proposal_file' => 'existing.pdf', 'gelombang' => 9]);
        $this->actingAs($student)->post('/pendaftaran/sempro', ['judul_ta' => 'Corrected', 'bentuk_ta' => 'Skripsi'])->assertSessionHas('success');
        $this->assertSame('Corrected', $row->fresh()->judul_ta);
        $this->assertSame('menunggu', $row->fresh()->status);
        $this->assertSame('menunggu', $row->fresh()->status_akademik);
        $this->assertSame('existing.pdf', $row->fresh()->proposal_file);
        $this->assertEquals(9, $row->fresh()->gelombang);
        foreach (['terjadwal', 'selesai'] as $status) {
            $row->update(['status' => $status]);
            $this->post('/pendaftaran/sempro', ['judul_ta' => 'Forbidden', 'bentuk_ta' => 'Skripsi'])->assertSessionHasErrors('judul_ta');
            $this->assertSame('Corrected', $row->fresh()->judul_ta);
        }
    }

    public function test_rejected_title_retry_clears_stale_approval_stamps(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $row = PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Rejected', 'bidang_penelitian' => 'AI', 'status' => 'ditolak', 'disetujui_oleh' => $lecturer->id, 'disetujui_at' => now()]);
        $this->actingAs($student)->post('/pendaftaran/judul', ['judul_ta' => 'Retry', 'bidang_penelitian' => 'AI', 'pembimbing_1' => $lecturer->id])->assertSessionHas('success');
        $this->assertSame('menunggu', $row->fresh()->status);
        $this->assertNull($row->fresh()->disetujui_at);
        $this->assertNull($row->fresh()->disetujui_oleh);
    }
}

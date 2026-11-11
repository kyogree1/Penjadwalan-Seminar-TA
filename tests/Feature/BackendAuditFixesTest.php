<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Notification;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penilaian;
use App\Models\Penjadwalan;
use App\Models\RescheduleRequest;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BackendAuditFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_reschedule_store_succeeds_without_optional_proposed_time_fields(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $dosen1 = User::factory()->create(['role' => 'dosen']);
        $dosen2 = User::factory()->create(['role' => 'dosen']);
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);

        $schedule = Penjadwalan::create([
            'tipe' => 'sempro',
            'pendaftaran_id' => 1,
            'mahasiswa_id' => $student->id,
            'penguji_1_id' => $dosen1->id,
            'penguji_2_id' => $dosen2->id,
            'tanggal' => '2026-11-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
        ]);

        $this->actingAs($dosen1)
            ->post('/pengajuan/reschedule', [
                'penjadwalan_id' => $schedule->id,
                'alasan' => 'Mendadak rapat fakultas',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reschedule_requests', [
            'penjadwalan_id' => $schedule->id,
            'diminta_oleh' => $dosen1->id,
            'status' => 'menunggu',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $kaprodi->id,
            'link' => '/kaprodi/persetujuan?section=reschedule',
        ]);
    }

    public function test_dosen_cannot_approve_reschedule_and_kaprodi_cannot_approve_conflicting_slot(): void
    {
        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);
        $dosen1 = User::factory()->create(['role' => 'dosen']);
        $dosen2 = User::factory()->create(['role' => 'dosen']);
        $dosen3 = User::factory()->create(['role' => 'dosen']);
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);

        $schedule1 = Penjadwalan::create([
            'tipe' => 'sempro',
            'pendaftaran_id' => 1,
            'mahasiswa_id' => $student1->id,
            'penguji_1_id' => $dosen1->id,
            'penguji_2_id' => $dosen2->id,
            'tanggal' => '2026-11-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
        ]);

        // Conflicting schedule involving dosen1 on 2026-11-22 13:00-14:30
        Penjadwalan::create([
            'tipe' => 'sempro',
            'pendaftaran_id' => 2,
            'mahasiswa_id' => $student2->id,
            'penguji_1_id' => $dosen1->id,
            'penguji_2_id' => $dosen3->id,
            'tanggal' => '2026-11-22',
            'jam_mulai' => '13:00:00',
            'jam_selesai' => '14:30:00',
            'status' => 'terpublikasi',
        ]);

        $req = RescheduleRequest::create([
            'penjadwalan_id' => $schedule1->id,
            'diminta_oleh' => $dosen1->id,
            'alasan' => 'Bentrok',
            'tanggal_usulan' => '2026-11-22',
            'jam_mulai_usulan' => '13:00:00',
            'jam_selesai_usulan' => '14:30:00',
            'status' => 'menunggu',
        ]);

        // Regular dosen must be blocked by role middleware
        $this->actingAs($dosen1)
            ->patch("/persetujuan/reschedule/{$req->id}", ['status' => 'disetujui'])
            ->assertSessionHas('error');
        $this->assertSame('menunggu', $req->fresh()->status);

        // Kaprodi must be blocked by schedule conflict validation
        $this->actingAs($kaprodi)
            ->patch("/persetujuan/reschedule/{$req->id}", ['status' => 'disetujui'])
            ->assertSessionHasErrors('tanggal');
        $this->assertSame('menunggu', $req->fresh()->status);
        $this->assertSame('2026-11-20', $schedule1->fresh()->tanggal->format('Y-m-d'));
    }

    public function test_update_plotting_validates_examiner_rules_conflicts_and_completed_status(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);
        $pembimbing = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Pembimbing']);
        $penguji1 = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Penguji A']);
        $penguji2 = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Penguji B']);
        $penguji3 = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Penguji C']);

        $period = SchedulePeriod::create([
            'name' => 'Wave 1',
            'tipe' => 'sempro',
            'gelombang' => 1,
            'capacity' => 20,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'is_open' => false,
            'slots' => [['tanggal' => '2026-11-25', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00']],
        ]);

        $schedule1 = Penjadwalan::create([
            'period_id' => $period->id,
            'tipe' => 'sempro',
            'pendaftaran_id' => 10,
            'mahasiswa_id' => $student1->id,
            'pembimbing_1_id' => $pembimbing->id,
            'penguji_1_id' => $penguji1->id,
            'penguji_2_id' => $penguji2->id,
            'tanggal' => '2026-11-25',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
        ]);

        // Existing booking for penguji3 on 2026-11-26 09:00-10:30
        Penjadwalan::create([
            'period_id' => $period->id,
            'tipe' => 'sempro',
            'pendaftaran_id' => 11,
            'mahasiswa_id' => $student2->id,
            'penguji_1_id' => $penguji3->id,
            'penguji_2_id' => $penguji2->id,
            'tanggal' => '2026-11-26',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
        ]);

        $this->actingAs($kaprodi);

        // Same examiner twice rejected
        $this->patch("/kaprodi/penjadwalan/plotting/{$schedule1->id}", [
            'penguji1' => $penguji1->name,
            'penguji2' => $penguji1->name,
        ])->assertSessionHasErrors('penguji2');

        // Supervisor acting as examiner rejected
        $this->patch("/kaprodi/penjadwalan/plotting/{$schedule1->id}", [
            'penguji1' => $pembimbing->name,
            'penguji2' => $penguji2->name,
        ])->assertSessionHasErrors('penguji1');

        // Conflict with another schedule rejected
        $this->patch("/kaprodi/penjadwalan/plotting/{$schedule1->id}", [
            'penguji1' => $penguji1->name,
            'penguji2' => $penguji3->name,
            'tanggal' => '2026-11-26',
            'mulai' => '09:00',
            'selesai' => '10:30',
        ])->assertSessionHasErrors('schedules');

        // Completed schedule cannot be modified
        $schedule1->update(['status' => 'selesai']);
        $this->patch("/kaprodi/penjadwalan/plotting/{$schedule1->id}", [
            'ruangan' => 'Ruang Baru',
        ])->assertSessionHasErrors('schedules');
    }

    public function test_completed_sempro_and_sidang_still_display_examiners_and_final_score(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $examiner1 = User::factory()->create(['role' => 'dosen', 'name' => 'Examiner Satu']);
        $examiner2 = User::factory()->create(['role' => 'dosen', 'name' => 'Examiner Dua']);

        $sempro = PendaftaranSempro::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Sempro Selesai',
            'bentuk_ta' => 'Skripsi',
            'status' => 'selesai',
            'status_akademik' => 'disetujui',
        ]);

        $jadwalSempro = Penjadwalan::create([
            'tipe' => 'sempro',
            'pendaftaran_id' => $sempro->id,
            'mahasiswa_id' => $student->id,
            'penguji_1_id' => $examiner1->id,
            'penguji_2_id' => $examiner2->id,
            'tanggal' => '2026-11-10',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'selesai',
        ]);

        Penilaian::create(['penjadwalan_id' => $jadwalSempro->id, 'dosen_id' => $examiner1->id, 'nilai' => 80]);
        Penilaian::create(['penjadwalan_id' => $jadwalSempro->id, 'dosen_id' => $examiner2->id, 'nilai' => 90]);

        $this->actingAs($student)
            ->get('/pendaftaran/sempro')
            ->assertInertia(fn (Assert $page) => $page
                ->has('pengujiList', 2)
                ->where('pelaksanaan.status', 'selesai')
                ->where('pelaksanaan.nilai_akhir', 85));

        $sidang = PendaftaranSidang::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Sidang Selesai',
            'status' => 'selesai',
            'status_akademik' => 'disetujui',
        ]);

        $jadwalSidang = Penjadwalan::create([
            'tipe' => 'sidang',
            'pendaftaran_id' => $sidang->id,
            'mahasiswa_id' => $student->id,
            'penguji_1_id' => $examiner1->id,
            'penguji_2_id' => $examiner2->id,
            'tanggal' => '2026-11-15',
            'jam_mulai' => '10:30:00',
            'jam_selesai' => '12:00:00',
            'status' => 'selesai',
        ]);

        Penilaian::create(['penjadwalan_id' => $jadwalSidang->id, 'dosen_id' => $examiner1->id, 'nilai' => 88]);
        Penilaian::create(['penjadwalan_id' => $jadwalSidang->id, 'dosen_id' => $examiner2->id, 'nilai' => 92]);

        $this->actingAs($student)
            ->get('/pendaftaran/sidang')
            ->assertInertia(fn (Assert $page) => $page
                ->has('pengujiList', 2)
                ->where('pelaksanaan.status', 'selesai')
                ->where('pelaksanaan.nilai_akhir', 90));
    }

    public function test_notification_links_point_to_existing_routes_and_academic_review_notifies_student(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $sempro = PendaftaranSempro::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Judul Uji',
            'bentuk_ta' => 'Skripsi',
            'status' => 'menunggu',
        ]);

        $this->actingAs($tendik)
            ->patch("/tendik/verifikasi/sempro/{$sempro->id}", ['status' => 'verifikasi_tendik'])
            ->assertSessionHas('success');

        $this->actingAs($kaprodi)
            ->patch("/kaprodi/persetujuan/sempro/{$sempro->id}", [
                'status_akademik' => 'disetujui',
                'catatan_kaprodi' => 'Siap ujian',
            ])
            ->assertSessionHas('success');

        $bimbingan = Bimbingan::create([
            'mahasiswa_id' => $student->id,
            'dosen_id' => $dosen->id,
            'tanggal' => now(),
            'rangkuman' => 'Bab 1',
            'keterangan' => 'Bimbingan Bab 1',
            'status' => 'process',
        ]);

        $this->actingAs($dosen)
            ->patch("/dosen/bimbingan/{$bimbingan->id}/paraf", ['status' => 'delivered'])
            ->assertSessionHas('success');

        $links = Notification::where('user_id', $student->id)->pluck('link')->all();
        $this->assertContains('/pendaftaran/sempro', $links);
        $this->assertContains('/pendaftaran/bimbingan', $links);
        foreach ($links as $link) {
            $this->assertStringNotContainsString('/mahasiswa/', $link);
        }
    }

    public function test_tendik_arsip_sorts_chronologically_newest_first(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $student1 = User::factory()->create(['role' => 'mahasiswa']);
        $student2 = User::factory()->create(['role' => 'mahasiswa']);

        $older = PendaftaranSempro::create([
            'mahasiswa_id' => $student1->id,
            'judul_ta' => 'Arsip Lebih Lama (25 Okt)',
            'bentuk_ta' => 'Skripsi',
            'status' => 'selesai',
        ]);
        $older->timestamps = false;
        $older->forceFill(['updated_at' => '2026-10-25 10:00:00'])->save();

        $newer = PendaftaranSidang::create([
            'mahasiswa_id' => $student2->id,
            'judul_ta' => 'Arsip Lebih Baru (02 Nov)',
            'status' => 'selesai',
        ]);
        $newer->timestamps = false;
        $newer->forceFill(['updated_at' => '2026-11-02 10:00:00'])->save();

        $this->actingAs($tendik)
            ->get('/tendik/arsip')
            ->assertInertia(fn (Assert $page) => $page
                ->has('arsipList', 2)
                ->where('arsipList.0.judul', 'Arsip Lebih Baru (02 Nov)')
                ->where('arsipList.1.judul', 'Arsip Lebih Lama (25 Okt)'));
    }
}

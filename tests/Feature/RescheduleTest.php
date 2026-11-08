<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\PendaftaranSempro;
use App\Models\Penjadwalan;
use App\Models\RescheduleRequest;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RescheduleTest extends TestCase
{
    use RefreshDatabase;

    private function createScheduleFixture(): array
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Mahasiswa Test', 'nim_nip' => '11211001']);
        $dosen1 = User::factory()->create(['role' => 'dosen', 'name' => 'Penguji Satu', 'nim_nip' => '198001012005011001']);
        $dosen2 = User::factory()->create(['role' => 'dosen', 'name' => 'Penguji Dua', 'nim_nip' => '198001012005011002']);
        $pembimbing = User::factory()->create(['role' => 'dosen', 'name' => 'Pembimbing Satu', 'nim_nip' => '198001012005011003']);
        $kaprodi = User::factory()->create(['role' => 'kaprodi', 'name' => 'Koordinator TA', 'nim_nip' => '197501012000011001']);

        $period = SchedulePeriod::create([
            'name' => 'Wave 1 Sempro',
            'tipe' => 'sempro',
            'gelombang' => 1,
            'capacity' => 20,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'is_open' => false,
            'is_published' => true,
            'slots' => [
                ['tanggal' => '2026-11-10', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
            ],
        ]);

        $reg = PendaftaranSempro::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul_ta' => 'Pengembangan Sistem AI',
            'bentuk_ta' => 'Reguler',
            'gelombang' => 1,
            'status' => 'terjadwal',
            'status_akademik' => 'disetujui',
        ]);

        $schedule = Penjadwalan::create([
            'tipe' => 'sempro',
            'pendaftaran_id' => $reg->id,
            'mahasiswa_id' => $mahasiswa->id,
            'pembimbing_1_id' => $pembimbing->id,
            'penguji_1_id' => $dosen1->id,
            'penguji_2_id' => $dosen2->id,
            'tanggal' => '2026-11-10',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
            'link_online' => 'Ruang Sidang 1',
        ]);

        return [$mahasiswa, $dosen1, $dosen2, $pembimbing, $kaprodi, $schedule];
    }

    public function test_involved_lecturer_can_submit_reschedule_request(): void
    {
        [$mahasiswa, $dosen1, , , $kaprodi, $schedule] = $this->createScheduleFixture();

        $this->actingAs($dosen1)
            ->post('/pengajuan/reschedule', [
                'penjadwalan_id' => $schedule->id,
                'alasan' => 'Bentrok Jadwal Mengajar',
                'catatan' => 'Mohon dimundurkan ke tanggal 12 Nov',
                'tanggal_usulan' => '2026-11-12',
                'jam_mulai_usulan' => '13:00:00',
                'jam_selesai_usulan' => '14:30:00',
                'ruangan_usulan' => 'Ruang Sidang 2',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reschedule_requests', [
            'penjadwalan_id' => $schedule->id,
            'diminta_oleh' => $dosen1->id,
            'alasan' => 'Bentrok Jadwal Mengajar',
            'status' => 'menunggu',
        ]);

        // Assert notification created for kaprodi
        $this->assertDatabaseHas('notifications', [
            'user_id' => $kaprodi->id,
            'title' => 'Permohonan Reschedule Jadwal',
        ]);
    }

    public function test_unauthorized_user_cannot_submit_reschedule_request(): void
    {
        [, , , , , $schedule] = $this->createScheduleFixture();
        $randomUser = User::factory()->create(['role' => 'dosen']);

        $this->actingAs($randomUser)
            ->post('/pengajuan/reschedule', [
                'penjadwalan_id' => $schedule->id,
                'alasan' => 'Iseng',
            ])
            ->assertSessionHasErrors('penjadwalan_id');

        $this->assertDatabaseCount('reschedule_requests', 0);
    }

    public function test_kaprodi_can_approve_reschedule_request_and_schedule_updates(): void
    {
        [$mahasiswa, $dosen1, , , $kaprodi, $schedule] = $this->createScheduleFixture();

        $req = RescheduleRequest::create([
            'penjadwalan_id' => $schedule->id,
            'diminta_oleh' => $dosen1->id,
            'alasan' => 'Bentrok',
            'tanggal_usulan' => '2026-11-15',
            'jam_mulai_usulan' => '13:30:00',
            'jam_selesai_usulan' => '15:00:00',
            'ruangan_usulan' => 'Lab AI',
            'status' => 'menunggu',
        ]);

        $this->actingAs($kaprodi)
            ->patch("/persetujuan/reschedule/{$req->id}", [
                'status' => 'disetujui',
                'catatan_kaprodi' => 'Disetujui silakan pakai Lab AI',
            ])
            ->assertSessionHas('success');

        $this->assertSame('disetujui', $req->fresh()->status);
        $this->assertSame('Disetujui silakan pakai Lab AI', $req->fresh()->catatan_kaprodi);
        $this->assertSame($kaprodi->id, $req->fresh()->diputuskan_oleh);

        // Schedule must be updated to the proposed date/time/room
        $this->assertSame('2026-11-15', $schedule->fresh()->tanggal->format('Y-m-d'));
        $this->assertSame('13:30:00', $schedule->fresh()->jam_mulai);
        $this->assertSame('15:00:00', $schedule->fresh()->jam_selesai);
        $this->assertSame('Lab AI', $schedule->fresh()->link_online);

        // Requester notified
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dosen1->id,
            'title' => 'Reschedule Jadwal Disetujui',
        ]);
    }

    public function test_kaprodi_can_reject_reschedule_request(): void
    {
        [, $dosen1, , , $kaprodi, $schedule] = $this->createScheduleFixture();

        $req = RescheduleRequest::create([
            'penjadwalan_id' => $schedule->id,
            'diminta_oleh' => $dosen1->id,
            'alasan' => 'Bentrok',
            'tanggal_usulan' => '2026-11-15',
            'jam_mulai_usulan' => '13:30:00',
            'jam_selesai_usulan' => '15:00:00',
            'status' => 'menunggu',
        ]);

        $this->actingAs($kaprodi)
            ->patch("/persetujuan/reschedule/{$req->id}", [
                'status' => 'ditolak',
                'catatan_kaprodi' => 'Ruangan dan slot tidak tersedia',
            ])
            ->assertSessionHas('success');

        $this->assertSame('ditolak', $req->fresh()->status);
        $this->assertSame('Ruangan dan slot tidak tersedia', $req->fresh()->catatan_kaprodi);

        // Schedule remains unchanged
        $this->assertSame('2026-11-10', $schedule->fresh()->tanggal->format('Y-m-d'));

        // Requester notified
        $this->assertDatabaseHas('notifications', [
            'user_id' => $dosen1->id,
            'title' => 'Reschedule Jadwal Ditolak',
        ]);
    }
}

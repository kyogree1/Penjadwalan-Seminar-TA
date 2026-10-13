<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class KaprodiPersetujuanTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_payload_includes_persisted_supervisors_and_lecturer_options(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']))
            ->get('/kaprodi/persetujuan')->assertInertia(fn (Assert $page) => $page
            ->where('dbJudul', [])->where('dbSempro', [])->where('dbSidang', [])->where('dosenOptions', []));
        $student = User::factory()->create(['role' => 'mahasiswa', 'nim_nip' => null]);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $judul = PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Aktual', 'bidang_penelitian' => 'AI', 'pembimbing_1_id' => $lecturer->id]);
        $sempro = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi', 'catatan' => 'Telaah']);
        $sidang = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sidang']);
        $this->get('/kaprodi/persetujuan')->assertInertia(fn (Assert $page) => $page
            ->where('dbJudul.0.id', $judul->id)->where('dbJudul.0.pembimbing_1_id', $lecturer->id)
            ->where('dbJudul.0.mahasiswa.nim_nip', null)->where('dbSempro.0.id', $sempro->id)
            ->where('dbSempro.0.catatan', 'Telaah')->where('dbSidang.0.id', $sidang->id)
            ->has('dosenOptions', 1)->where('dosenOptions.0.id', $lecturer->id));
    }

    public function test_supervisor_validation_is_atomic_and_supported_assignment_persists(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $lecturer = User::factory()->create(['role' => 'dosen']);
        $judul = PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Aktual', 'bidang_penelitian' => 'AI']);
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));
        foreach ([$student->id, 999999, 'invalid'] as $id) {
            $this->patch('/kaprodi/persetujuan/judul/'.$judul->id, ['status' => 'disetujui', 'pembimbing_1_id' => $id])
                ->assertSessionHasErrors('pembimbing_1_id');
            $this->assertSame('menunggu', $judul->fresh()->status);
            $this->assertNull($judul->fresh()->pembimbing_1_id);
        }
        $this->patch('/kaprodi/persetujuan/judul/'.$judul->id, ['status' => 'invalid'])->assertSessionHasErrors('status');
        $this->patch('/kaprodi/persetujuan/judul/'.$judul->id, ['status' => 'disetujui', 'pembimbing_1_id' => $lecturer->id, 'catatan_kaprodi' => 'Disetujui'])
            ->assertSessionHas('success');
        $this->assertSame($lecturer->id, $judul->fresh()->pembimbing_1_id);
        $this->assertSame('Disetujui', $judul->fresh()->catatan_kaprodi);
    }

    public function test_academic_notes_reject_invalid_revision_and_preserve_overlapping_type(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $sempro = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi']);
        $sidang = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sidang']);
        $this->assertSame($sempro->id, $sidang->id);
        $this->actingAs(User::factory()->create(['role' => 'koordinator']));
        foreach (['sempro' => $sempro, 'sidang' => $sidang] as $type => $row) {
            foreach (['revisi', 'disetujui', 'invalid'] as $status) {
                $this->patch('/kaprodi/persetujuan/'.$type.'/'.$row->id, ['status' => $status])->assertSessionHasErrors('status');
                $this->assertSame('menunggu', $row->fresh()->status);
            }
            $this->patch('/kaprodi/persetujuan/'.$type.'/'.$row->id, ['status' => 'menunggu', 'catatan' => 'Telaah tersimpan'])->assertSessionHas('success');
            $this->assertSame('Telaah tersimpan', $row->fresh()->catatan_kaprodi);
            $this->assertNull($row->fresh()->catatan);
            $this->patch('/kaprodi/persetujuan/'.$type.'/999999', ['status' => 'menunggu'])->assertNotFound();
        }
    }
}

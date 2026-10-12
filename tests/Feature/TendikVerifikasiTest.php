<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TendikVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_endpoints_validate_supported_actions_and_persist_only_target_type(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $sempro = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi']);
        $sidang = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sidang']);
        $this->assertSame($sempro->id, $sidang->id);
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        foreach (['sempro' => $sempro, 'sidang' => $sidang] as $type => $row) {
            $url = '/tendik/verifikasi/'.$type.'/'.$row->id;
            foreach (['revisi', 'disetujui', 'terjadwal', 'selesai', '', null] as $status) {
                $this->from('/tendik/verifikasi')->patch($url, ['status' => $status])
                    ->assertRedirect('/tendik/verifikasi')->assertSessionHasErrors('status');
                $this->assertSame('menunggu', $row->fresh()->status);
            }
            $this->patch($url, ['status' => 'ditolak', 'catatan' => ['invalid']])->assertSessionHasErrors('catatan');
            $this->assertSame('menunggu', $row->fresh()->status);
            foreach (['verifikasi_tendik', 'ditolak', 'menunggu'] as $status) {
                $this->from('/tendik/verifikasi')->patch($url, ['status' => $status, 'catatan' => 'Catatan aktual'])
                    ->assertRedirect('/tendik/verifikasi')->assertSessionHas('success');
                $this->assertSame($status, $row->fresh()->status);
                $this->assertSame('Catatan aktual', $row->fresh()->catatan);
                $other = $type === 'sempro' ? $sidang : $sempro;
                $this->assertSame('menunggu', $other->fresh()->status);
            }
            $this->patch('/tendik/verifikasi/'.$type.'/999999', ['status' => 'ditolak'])->assertNotFound();
        }
        $this->get('/tendik/verifikasi')->assertInertia(fn (Assert $page) => $page
            ->where('pengajuan.0.catatan', 'Catatan aktual')->where('pengajuan.1.catatan', 'Catatan aktual'));
    }

    public function test_existing_role_gates_deny_reads_and_mutations_without_changing_records(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $sempro = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi']);
        $sidang = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sidang']);
        $this->get('/tendik/verifikasi')->assertRedirect('/login');
        foreach (['sempro' => $sempro, 'sidang' => $sidang] as $type => $row) {
            $this->patch('/tendik/verifikasi/'.$type.'/'.$row->id, ['status' => 'ditolak'])->assertRedirect('/login');
        }
        foreach (['mahasiswa', 'dosen', 'kaprodi', 'koordinator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/tendik/verifikasi')->assertRedirect()->assertSessionHas('error');
            foreach (['sempro' => $sempro, 'sidang' => $sidang] as $type => $row) {
                $this->patch('/tendik/verifikasi/'.$type.'/'.$row->id, ['status' => 'ditolak'])
                    ->assertRedirect()->assertSessionHas('error');
                $this->assertSame('menunggu', $row->fresh()->status);
                $this->assertNull($row->fresh()->catatan);
            }
        }
    }

    public function test_live_contract_serializes_persisted_identity_status_and_document_paths(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']))
            ->get('/tendik/verifikasi')->assertInertia(fn (Assert $page) => $page
            ->component('Tendik/Verifikasi')->where('pengajuan', []));
        $student = User::factory()->create(['role' => 'mahasiswa', 'nim_nip' => null]);
        $sempro = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal aktual', 'bentuk_ta' => 'Skripsi', 'proposal_file' => 'sempro/proposal.pdf']);
        $sidang = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Sidang aktual', 'status' => 'terjadwal']);
        $this->get('/tendik/verifikasi')->assertInertia(fn (Assert $page) => $page
            ->has('pengajuan', 2)->missing('dbSempro')->missing('dbSidang')
            ->where('pengajuan.0.id', $sempro->id)->where('pengajuan.0.tipe', 'sempro')
            ->where('pengajuan.0.nama', $student->name)->where('pengajuan.0.nim', null)
            ->where('pengajuan.0.judul', 'Proposal aktual')->where('pengajuan.0.status', 'menunggu')
            ->where('pengajuan.0.catatan', null)->has('pengajuan.0.tanggalDaftar')
            ->has('pengajuan.0.dokumen', 4)->where('pengajuan.0.dokumen.1.path', 'sempro/proposal.pdf')
            ->where('pengajuan.0.dokumen.0.path', null)->missing('pengajuan.0.dokumen.0.valid')
            ->where('pengajuan.1.id', $sidang->id)->where('pengajuan.1.tipe', 'sidang')
            ->where('pengajuan.1.status', 'terjadwal')->has('pengajuan.1.dokumen', 3));
    }
}

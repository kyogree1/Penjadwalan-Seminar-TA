<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class R3ReviewLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_review_has_separate_status_and_note(): void
    {
        $row = PendaftaranSempro::create(['mahasiswa_id' => User::factory()->create(['role' => 'mahasiswa'])->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi', 'status' => 'verifikasi_tendik', 'catatan' => 'Admin note']);
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']))
            ->patch('/kaprodi/persetujuan/sempro/'.$row->id, ['status_akademik' => 'revisi', 'catatan_kaprodi' => 'Academic revision'])->assertSessionHas('success');
        $this->assertSame('verifikasi_tendik', $row->fresh()->status);
        $this->assertSame('Admin note', $row->fresh()->catatan);
        $this->assertSame('revisi', $row->fresh()->status_akademik);
        $this->assertSame('Academic revision', $row->fresh()->catatan_kaprodi);
        $this->patch('/kaprodi/persetujuan/sempro/'.$row->id, ['status' => 'verifikasi_tendik', 'catatan' => 'Legacy academic note'])->assertSessionHas('success');
        $this->assertSame('Admin note', $row->fresh()->catatan);
        $this->assertSame('Legacy academic note', $row->fresh()->catatan_kaprodi);
    }

    public function test_title_revision_and_rejection_have_no_approval_stamp_and_supervisors_are_distinct(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $row = PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Title', 'bidang_penelitian' => 'AI']);
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));
        $url = '/kaprodi/persetujuan/judul/'.$row->id;
        $this->patch($url, ['status' => 'disetujui', 'pembimbing_1_id' => $dosen->id, 'pembimbing_2_id' => $dosen->id])->assertSessionHasErrors('pembimbing_2_id');
        $this->patch($url, ['status' => 'revisi', 'catatan_kaprodi' => 'Revise title'])->assertSessionHas('success');
        $this->assertSame('revisi', $row->fresh()->status);
        $this->assertNull($row->fresh()->disetujui_at);
        $this->assertNull($row->fresh()->disetujui_oleh);
        $this->patch($url, ['status' => 'draft'])->assertSessionHasErrors('status');
    }

    public function test_verified_changes_require_review_and_active_exams_cannot_be_reopened(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $row = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Original', 'bentuk_ta' => 'Skripsi', 'status' => 'verifikasi_tendik', 'status_akademik' => 'disetujui']);
        $this->actingAs($student)->post('/pendaftaran/sempro', ['judul_ta' => 'Changed', 'bentuk_ta' => 'Skripsi'])->assertSessionHas('success');
        $this->assertSame('menunggu', $row->fresh()->status);
        $this->assertSame('menunggu', $row->fresh()->status_akademik);
        $row->update(['status' => 'selesai']);
        $this->post('/pendaftaran/sempro', ['judul_ta' => 'Reopened', 'bentuk_ta' => 'Skripsi'])->assertSessionHasErrors('judul_ta');
        $this->actingAs(User::factory()->create(['role' => 'tendik']))->patch('/tendik/verifikasi/sempro/'.$row->id, ['status' => 'menunggu'])->assertSessionHasErrors('status');
        $this->assertSame('selesai', $row->fresh()->status);
    }

    public function test_title_supervisors_require_ids_and_resubmission_clears_approval(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);
        $row = PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Approved', 'bidang_penelitian' => 'AI', 'status' => 'disetujui', 'disetujui_oleh' => $dosen->id, 'disetujui_at' => now()]);
        $this->actingAs($student)->post('/pendaftaran/judul', ['judul_ta' => 'Changed', 'bidang_penelitian' => 'AI', 'pembimbing_1' => $dosen->name])->assertSessionHasErrors('pembimbing_1');
        $this->post('/pendaftaran/judul', ['judul_ta' => 'Changed', 'bidang_penelitian' => 'AI', 'pembimbing_1' => $dosen->id])->assertSessionHas('success');
        $this->assertSame($dosen->id, $row->fresh()->pembimbing_1_id);
        $this->assertSame('menunggu', $row->fresh()->status);
        $this->assertNull($row->fresh()->disetujui_at);
        $this->assertNull($row->fresh()->disetujui_oleh);
    }

    public function test_review_role_gates_preserve_notes_and_decisions(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $row = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi']);
        foreach (['mahasiswa', 'dosen', 'tendik'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->patch('/kaprodi/persetujuan/sempro/'.$row->id, ['status_akademik' => 'disetujui', 'catatan_kaprodi' => 'Unauthorized'])->assertSessionHas('error');
            $this->assertSame('menunggu', $row->fresh()->status_akademik);
            $this->assertNull($row->fresh()->catatan_kaprodi);
        }
    }

    public function test_academic_enum_is_enforced_by_configured_sqlite_schema(): void
    {
        $row = PendaftaranSempro::create(['mahasiswa_id' => User::factory()->create(['role' => 'mahasiswa'])->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi']);
        $this->expectException(QueryException::class);
        DB::table('pendaftaran_sempro')->where('id', $row->id)->update(['status_akademik' => 'terjadwal']);
    }

    public function test_kaprodi_cannot_forge_administrative_or_exam_completion(): void
    {
        $row = PendaftaranSempro::create(['mahasiswa_id' => User::factory()->create(['role' => 'mahasiswa'])->id, 'judul_ta' => 'Proposal', 'bentuk_ta' => 'Skripsi']);
        $this->actingAs(User::factory()->create(['role' => 'kaprodi']));
        foreach (['verifikasi_tendik', 'terjadwal', 'selesai', 'ditolak'] as $status) {
            $this->patch('/kaprodi/persetujuan/sempro/'.$row->id, ['status' => $status])->assertSessionHasErrors('status');
            $this->assertSame('menunggu', $row->fresh()->status);
        }
    }
}

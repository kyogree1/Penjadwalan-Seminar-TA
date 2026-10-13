<?php

namespace Tests\Feature;

use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExamDocumentHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_pdf_retains_csp_and_private_headers_through_http_kernel(): void
    {
        Storage::fake('local');
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $examiner = User::factory()->create(['role' => 'dosen']);
        $path = 'dokumen/sidang/'.$student->id.'/draft.pdf';
        Storage::disk('local')->put($path, '%PDF-1.4 header regression');
        $registration = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Header regression', 'draft_laporan_file' => $path]);
        $exam = Penjadwalan::create(['tipe' => 'sidang', 'pendaftaran_id' => $registration->id, 'mahasiswa_id' => $student->id, 'penguji_1_id' => $examiner->id, 'tanggal' => '2026-10-11', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:00:00', 'status' => 'terpublikasi']);
        $response = $this->actingAs($examiner)->get('/dosen/penilaian/'.$exam->id.'/dokumen/draft_laporan_file');
        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Security-Policy', "sandbox; default-src 'none'")
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('%PDF-1.4 header regression', $response->streamedContent());
    }
}

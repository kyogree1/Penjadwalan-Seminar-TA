<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyDocumentRolloutTest extends TestCase
{
    use RefreshDatabase;

    public function test_conflicts_missing_and_unsafe_paths_block_all_copies(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $user = User::factory()->create();
        $path = "dokumen/sidang/{$user->id}/legacy.pdf";
        DB::table('pendaftaran_sidang')->insert([
            'mahasiswa_id' => $user->id, 'judul_ta' => 'Test',
            'draft_laporan_file' => $path, 'turnitin_file' => '../outside.pdf',
            'skor_iaet_file' => "dokumen/sidang/{$user->id}/missing.pdf",
        ]);
        Storage::disk('public')->put($path, 'source');
        Storage::disk('local')->put($path, 'conflict');
        $this->artisan('documents:relocate-legacy', ['--execute' => true, '--quiesced' => true])
            ->expectsOutput('mode=execute candidates=0 blocked=3')->assertFailed();
        $this->assertSame('conflict', Storage::disk('local')->get($path));
        $this->assertSame('source', Storage::disk('public')->get($path));
    }

    public function test_default_is_read_only_and_execute_preserves_public_source(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $user = User::factory()->create();
        $path = "dokumen/sempro/{$user->id}/legacy.pdf";
        DB::table('pendaftaran_sempro')->insert([
            'mahasiswa_id' => $user->id, 'judul_ta' => 'Test', 'bentuk_ta' => 'Skripsi', 'proposal_file' => $path,
        ]);
        Storage::disk('public')->put($path, '%PDF-1.4 disposable fixture');

        $this->artisan('documents:relocate-legacy')->assertSuccessful();
        Storage::disk('local')->assertMissing($path);
        Storage::disk('public')->assertExists($path);
        $this->artisan('documents:relocate-legacy', ['--execute' => true])->assertFailed();
        Storage::disk('local')->assertMissing($path);
        $this->artisan('documents:relocate-legacy', ['--execute' => true, '--quiesced' => true])->assertSuccessful();
        $this->assertSame(Storage::disk('public')->get($path), Storage::disk('local')->get($path));
        Storage::disk('public')->assertExists($path);
        $this->artisan('documents:relocate-legacy', ['--execute' => true, '--quiesced' => true])->assertSuccessful();
        $this->assertSame($path, DB::table('pendaftaran_sempro')->value('proposal_file'));
    }
}

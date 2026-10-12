<?php

namespace App\Console\Commands;

use App\Http\Controllers\Dosen\ExamDocumentController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class RelocateLegacyDocuments extends Command
{
    protected $signature = 'documents:relocate-legacy {--execute : Copy verified files; never delete public originals} {--quiesced : Confirm uploads and document writes are stopped}';

    protected $description = 'Read-only legacy document inventory by default; opt-in verified private copies';

    public function handle(): int
    {
        if ($this->option('execute') && ! $this->option('quiesced')) {
            $this->error('Execution requires --quiesced and operator-controlled maintenance.');

            return self::FAILURE;
        }
        try {
            $public = Storage::disk('public');
            $private = Storage::disk('local');
            foreach (['public', 'local'] as $disk) {
                if (config("filesystems.disks.{$disk}.driver") !== 'local') {
                    throw new RuntimeException('Only local filesystem contracts are supported.');
                }
            }
            if (realpath($public->path('')) === realpath($private->path('')) && realpath($public->path('')) !== false) {
                throw new RuntimeException('Public and private storage roots must differ.');
            }
            $plan = [];
            $blocked = 0;
            foreach (['sempro', 'sidang'] as $type) {
                DB::table('pendaftaran_'.$type)->orderBy('id')->chunkById(100, function ($rows) use ($type, $public, $private, &$plan, &$blocked) {
                    foreach ($rows as $row) {
                        foreach (ExamDocumentController::fields($type) as $field) {
                            $path = $row->{$field};
                            if ($path === null || $path === '') {
                                continue;
                            }
                            // Accept only the exact historical upload contract, never arbitrary paths.
                            if (! preg_match('~^dokumen/'.$type.'/'.(int) $row->mahasiswa_id.'/[A-Za-z0-9_-]+\.pdf$~D', $path)) {
                                $blocked++;

                                continue;
                            }
                            foreach ([$public, $private] as $disk) {
                                $cursor = rtrim($disk->path(''), '/\\');
                                if (is_link($cursor)) {
                                    throw new RuntimeException('Symlink storage roots are unsupported.');
                                }
                                foreach (explode('/', $path) as $component) {
                                    $cursor .= DIRECTORY_SEPARATOR.$component;
                                    if (is_link($cursor)) {
                                        throw new RuntimeException('Symlink document paths are unsupported.');
                                    }
                                }
                            }
                            if (! $public->exists($path)) {
                                if (! $private->exists($path)) {
                                    $blocked++;
                                }

                                continue;
                            }
                            $hash = hash_file('sha256', $public->path($path));
                            if ($private->exists($path) && hash_file('sha256', $private->path($path)) !== $hash) {
                                $blocked++;

                                continue;
                            }
                            $plan[$path] = $hash;
                        }
                    }
                });
            }
            $this->line('mode='.($this->option('execute') ? 'execute' : 'dry-run').' candidates='.count($plan).' blocked='.$blocked);
            if ($blocked) {
                $this->error('Resolve missing, unsafe, or conflicting references before executing; no files copied.');

                return self::FAILURE;
            }
            if (! $this->option('execute')) {
                return self::SUCCESS;
            }
            foreach ($plan as $path => $hash) {
                if (hash_file('sha256', $public->path($path)) !== $hash) {
                    throw new RuntimeException('Source changed after inventory. Stop and rerun after quiescing.');
                }
                if (! $private->exists($path)) {
                    $parent = dirname($private->path($path));
                    if (! is_dir($parent) && ! mkdir($parent, 0700, true) && ! is_dir($parent)) {
                        throw new RuntimeException('Cannot create private directory.');
                    }
                    // Exclusive creation: never overwrite a concurrent destination.
                    $out = fopen($private->path($path), 'xb');
                    if ($out === false) {
                        throw new RuntimeException('Cannot exclusively create destination.');
                    }
                    $in = $public->readStream($path);
                    try {
                        if (! is_resource($in) || stream_copy_to_stream($in, $out) === false) {
                            throw new RuntimeException('Copy failed; public original retained.');
                        }
                    } finally {
                        fclose($out);
                        if (is_resource($in)) {
                            fclose($in);
                        }
                    }
                }
                if (hash_file('sha256', $private->path($path)) !== $hash || hash_file('sha256', $public->path($path)) !== $hash) {
                    throw new RuntimeException('Verification failed; public originals retained. Investigate partial private copies.');
                }
            }
            $this->info('Private copies verified; database paths unchanged. Public originals retained: confidentiality rollout is NOT complete until operator retirement.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            // Do not print underlying exception text: database/storage errors can contain secrets.
            $this->error('Rollout stopped ('.class_basename($exception).'). Check restricted operator logs/configuration; originals were not deleted.');

            return self::FAILURE;
        }
    }
}

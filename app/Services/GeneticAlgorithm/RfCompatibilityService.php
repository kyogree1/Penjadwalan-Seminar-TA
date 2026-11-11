<?php

namespace App\Services\GeneticAlgorithm;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class RfCompatibilityService
{
    protected string $scriptPath;

    protected string $modelPath;

    public function __construct()
    {
        $this->scriptPath = base_path('scripts/rf_compatibility.py');
        $this->modelPath = base_path('models/rf/paket_model_rf_runtime_private.zip');
    }

    public function getCompatibility(array $candidates, array $lecturers): array
    {
        if (empty($candidates) || empty($lecturers) || ! file_exists($this->modelPath) || ! file_exists($this->scriptPath)) {
            return [];
        }

        $uvPath = $this->resolveUvBinary();
        if (! $uvPath) {
            Log::warning('RfCompatibilityService: binary `uv` not found, using neutral score.');

            return [];
        }

        $payload = json_encode([
            'candidates' => $candidates,
            'lecturers' => array_map(fn ($l) => ['id' => $l['id'], 'name' => $l['name']], $lecturers),
        ], JSON_THROW_ON_ERROR);

        $command = "{$uvPath} run --python 3.12 --with scikit-learn==1.6.1,pandas,joblib python3 {$this->scriptPath}";

        try {
            $process = Process::timeout(30)->input($payload)->run($command);

            if (! $process->successful()) {
                Log::warning('RfCompatibilityService process failed: '.$process->errorOutput());

                return [];
            }

            $output = json_decode($process->output(), true);
            if (isset($output['status']) && $output['status'] === 'success' && isset($output['compatibility'])) {
                $compatibility = [];
                foreach ($output['compatibility'] as $pId => $scores) {
                    $compatibility[(int) $pId] = [];
                    foreach ($scores as $dId => $score) {
                        $compatibility[(int) $pId][(int) $dId] = (float) $score;
                    }
                }

                return $compatibility;
            }

            Log::warning('RfCompatibilityService invalid response: '.$process->output());

            return [];
        } catch (\Throwable $e) {
            Log::warning('RfCompatibilityService exception: '.$e->getMessage());

            return [];
        }
    }

    protected function resolveUvBinary(): ?string
    {
        $configured = getenv('UV_PATH') ?: ($_ENV['UV_PATH'] ?? null);
        if ($configured && is_file($configured)) {
            return $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $where = trim((string) shell_exec('where uv 2>NUL'));
            if ($where) {
                $firstLine = trim(strtok($where, "\r\n"));
                if ($firstLine && is_file($firstLine)) {
                    return $firstLine;
                }
            }

            $profile = getenv('USERPROFILE') ?: getenv('HOME');
            if ($profile) {
                foreach (["{$profile}\\.local\\bin\\uv.exe", "{$profile}\\.cargo\\bin\\uv.exe"] as $candidate) {
                    if (is_file($candidate)) {
                        return $candidate;
                    }
                }
            }

            return null;
        }

        $which = trim((string) shell_exec('which uv 2>/dev/null'));
        if ($which && is_executable($which)) {
            return $which;
        }

        $home = getenv('HOME') ?: '/home/azkaaa';
        $fallback = "{$home}/.local/bin/uv";
        if (is_executable($fallback)) {
            return $fallback;
        }

        return null;
    }
}

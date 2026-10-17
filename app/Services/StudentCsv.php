<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StudentCsv
{
    public const HEADER = ['name', 'nim', 'angkatan', 'jurusan', 'prodi', 'email', 'no_telpon', 'status_ta'];
    public const HEADER_ID = ['nama', 'nim', 'angkatan', 'jurusan', 'prodi', 'email', 'no_telpon', 'status_ta'];

    public function preview(UploadedFile $file): array
    {
        $text = file_get_contents($file->getRealPath());
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        if (! mb_check_encoding($text, 'UTF-8') || str_contains($text, "\0")) {
            return $this->invalid('File harus berupa CSV UTF-8.');
        }

        // Validate quotes
        $state = 'start';
        for ($i = 0; $i < strlen($text); $i++) {
            $c = $text[$i];
            if ($state === 'quoted') {
                if ($c === '"') {
                    if (($text[$i + 1] ?? '') === '"') {
                        $i++;
                    } else {
                        $state = 'closed';
                    }
                }
            } elseif ($c === ',' || $c === "\n" || $c === "\r") {
                $state = 'start';
            } elseif ($state === 'start' && $c === '"') {
                $state = 'quoted';
            } elseif ($c === '"' || $state === 'closed') {
                return $this->invalid('Tanda kutip CSV tidak valid.');
            } else {
                $state = 'plain';
            }
        }
        if ($state === 'quoted') {
            return $this->invalid('Tanda kutip CSV belum ditutup.');
        }

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $text);
        rewind($stream);
        $rawHeader = fgetcsv($stream, 0, ',', '"', '');
        if (! is_array($rawHeader)) {
            fclose($stream);
            return $this->invalid('Format CSV tidak dapat dibaca.');
        }

        $normalizedHeader = array_map(fn ($h) => strtolower(trim($h ?? '')), $rawHeader);

        // Map header aliases
        $headerMap = [
            'nama' => 'name',
            'name' => 'name',
            'nim' => 'nim',
            'angkatan' => 'angkatan',
            'jurusan' => 'jurusan',
            'prodi' => 'prodi',
            'program studi' => 'prodi',
            'email' => 'email',
            'no_telpon' => 'no_telpon',
            'no telpon' => 'no_telpon',
            'nomor telepon' => 'no_telpon',
            'no hp' => 'no_telpon',
            'telepon' => 'no_telpon',
            'phone' => 'no_telpon',
            'status_ta' => 'status_ta',
            'status ta' => 'status_ta',
            'status' => 'status_ta',
        ];

        $mappedKeys = [];
        foreach ($normalizedHeader as $col) {
            $mappedKeys[] = $headerMap[$col] ?? $col;
        }

        $requiredKeys = ['name', 'nim', 'angkatan', 'jurusan', 'prodi', 'email'];
        $missing = array_diff($requiredKeys, $mappedKeys);
        if (! empty($missing)) {
            fclose($stream);
            return $this->invalid('Header kolom wajib: nama/name, nim, angkatan, jurusan, prodi, email, no_telpon, status_ta.');
        }

        $rows = [];
        $seenNim = [];
        $seenEmail = [];

        while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            if ($values === [null] || empty(array_filter($values))) {
                continue;
            }
            $rowNum = count($rows) + 2;
            if (count($values) !== count($mappedKeys)) {
                $rows[] = ['row' => $rowNum, 'data' => null, 'errors' => ['row' => ['Jumlah kolom tidak sesuai header.']]];
                continue;
            }

            $rawRow = array_combine($mappedKeys, array_map(fn ($v) => trim($v ?? ''), $values));
            $data = [
                'name' => $rawRow['name'] ?? '',
                'nim' => $rawRow['nim'] ?? '',
                'angkatan' => $rawRow['angkatan'] ?? '',
                'jurusan' => $rawRow['jurusan'] ?? '',
                'prodi' => $rawRow['prodi'] ?? '',
                'email' => $rawRow['email'] ?? '',
                'no_telpon' => $rawRow['no_telpon'] ?? '',
                'status_ta' => ! empty($rawRow['status_ta']) ? $rawRow['status_ta'] : 'Aktif TA',
            ];

            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'nim' => ['required', 'string', 'max:50', Rule::unique('users', 'username'), Rule::unique('users', 'nim_nip')],
                'angkatan' => ['required', 'string', 'max:10'],
                'jurusan' => ['required', 'string', 'max:255'],
                'prodi' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'no_telpon' => ['nullable', 'string', 'max:50'],
                'status_ta' => ['nullable', 'string', 'in:Aktif TA,Sempro,Bimbingan,Sidang Akhir,Lulus'],
            ]);

            $errors = $validator->errors()->toArray();
            $nimKey = strtolower($data['nim']);
            $emailKey = strtolower($data['email']);

            if (isset($seenNim[$nimKey])) {
                $errors['nim'][] = 'NIM duplikat dalam file CSV.';
            }
            if (isset($seenEmail[$emailKey])) {
                $errors['email'][] = 'Email duplikat dalam file CSV.';
            }

            $seenNim[$nimKey] = true;
            $seenEmail[$emailKey] = true;

            $rows[] = ['row' => $rowNum, 'data' => $data, 'errors' => $errors];

            if (count($rows) > 500) {
                fclose($stream);
                return $this->invalid('Maksimal 500 baris per file.');
            }
        }
        fclose($stream);

        if (! $rows) {
            return $this->invalid('CSV tidak memiliki baris data.');
        }

        return [
            'valid' => ! collect($rows)->contains(fn ($r) => count($r['errors']) > 0),
            'rows' => $rows,
            'errors' => [],
        ];
    }

    private function invalid(string $message): array
    {
        return ['valid' => false, 'rows' => [], 'errors' => [$message]];
    }
}

<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LecturerCsv
{
    public const HEADER = ['name', 'email', 'nip', 'prodi', 'jabatan'];

    public function preview(UploadedFile $file): array
    {
        $text = file_get_contents($file->getRealPath());
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        if (! mb_check_encoding($text, 'UTF-8') || str_contains($text, "\0")) {
            return $this->invalid('File harus berupa CSV UTF-8.');
        }
        // Reject malformed quotes instead of silently accepting fgetcsv's recovery.
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
        $header = fgetcsv($stream, 0, ',', '"', '');
        if ($header !== self::HEADER) {
            fclose($stream);

            return $this->invalid('Header wajib: '.implode(',', self::HEADER));
        }
        $rows = [];
        $seenNip = [];
        $seenEmail = [];
        while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            if ($values === [null]) {
                continue;
            }
            $row = count($rows) + 2;
            if (count($values) !== count(self::HEADER)) {
                $rows[] = ['row' => $row, 'data' => null, 'errors' => ['row' => 'Jumlah kolom harus 5.']];

                continue;
            }
            $data = array_combine(self::HEADER, array_map(fn ($v) => trim($v ?? ''), $values));
            $validator = Validator::make($data, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'nip' => ['required', 'string', 'max:50', Rule::unique('users', 'username'), Rule::unique('users', 'nim_nip')],
                'prodi' => ['required', 'string', 'max:100'],
                'jabatan' => ['nullable', 'string', 'max:100'],
            ]);
            $errors = $validator->errors()->toArray();
            $email = strtolower($data['email']);
            if (isset($seenNip[$data['nip']])) {
                $errors['nip'][] = 'NIP duplikat dalam file.';
            }
            if (isset($seenEmail[$email])) {
                $errors['email'][] = 'Email duplikat dalam file.';
            }
            $seenNip[$data['nip']] = true;
            $seenEmail[$email] = true;
            $rows[] = ['row' => $row, 'data' => $data, 'errors' => $errors];
            if (count($rows) > 500) {
                fclose($stream);

                return $this->invalid('Maksimal 500 baris per file.');
            }
        }
        fclose($stream);
        if (! $rows) {
            return $this->invalid('CSV tidak memiliki baris data.');
        }

        return ['valid' => ! collect($rows)->contains(fn ($row) => count($row['errors']) > 0), 'rows' => $rows, 'errors' => []];
    }

    private function invalid(string $message): array
    {
        return ['valid' => false, 'rows' => [], 'errors' => [$message]];
    }
}

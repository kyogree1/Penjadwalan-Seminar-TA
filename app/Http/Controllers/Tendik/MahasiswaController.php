<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class MahasiswaController extends Controller
{
    public function index(): Response
    {
        $students = User::where('role', 'mahasiswa')
            ->select('id', 'name', 'email', 'username', 'nim_nip', 'prodi', 'created_at')
            ->get();

        return Inertia::render('Tendik/Mahasiswa', [
            'dbStudents' => $students,
        ]);
    }

    public function template()
    {
        return response()->streamDownload(function () {
            echo "name,email\n";
        }, 'template-mahasiswa.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function preview(Request $request)
    {
        $rows = $this->csvRows($request);

        return response()->json(['rows' => $rows, 'count' => count($rows)]);
    }

    public function import(Request $request)
    {
        $rows = $this->csvRows($request);
        DB::transaction(function () use ($rows) {
            foreach ($rows as $index => $data) {
                $this->validateCsvRow($data, $index);
                User::create([...$data, 'role' => 'mahasiswa', 'password' => Str::random(40)]);
            }
        });

        return redirect()->back()->with('success', 'CSV akun mahasiswa diimpor. Provisioning akses terpisah.');
    }

    private function validateCsvRow(array $data, int $index): void
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
        ]);
        if ($validator->fails()) {
            throw ValidationException::withMessages(['file' => 'Baris '.($index + 2).': '.$validator->errors()->first()]);
        }
    }

    private function csvRows(Request $request): array
    {
        $request->validate(['file' => ['required', 'file', 'max:1024', 'extensions:csv']]);
        $content = file_get_contents($request->file('file')->getRealPath());
        if (! mb_check_encoding($content, 'UTF-8') || str_contains($content, "\0")) {
            throw ValidationException::withMessages(['file' => 'CSV harus berupa teks UTF-8.']);
        }
        // fgetcsv recovers malformed quoting; reject it before any persistence.
        $state = 'start';
        for ($i = 0; $i < strlen($content); $i++) {
            $char = $content[$i];
            if ($state === 'quoted') {
                if ($char === '"') {
                    if (($content[$i + 1] ?? '') === '"') {
                        $i++;
                    } else {
                        $state = 'closed';
                    }
                }
            } elseif (in_array($char, [',', "\n", "\r"])) {
                $state = 'start';
            } elseif ($state === 'start' && $char === '"') {
                $state = 'quoted';
            } elseif ($char === '"' || $state === 'closed') {
                throw ValidationException::withMessages(['file' => 'Tanda kutip CSV tidak valid.']);
            } else {
                $state = 'plain';
            }
        }
        if ($state === 'quoted') {
            throw ValidationException::withMessages(['file' => 'Tanda kutip CSV belum ditutup.']);
        }
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        try {
            if (fgetcsv($stream, null, ',', '"', '') !== ['name', 'email']) {
                throw ValidationException::withMessages(['file' => 'Header CSV harus name,email.']);
            }
            $rows = [];
            $emails = [];
            while (($row = fgetcsv($stream, null, ',', '"', '')) !== false) {
                if ($row === [null]) {
                    continue;
                }
                if (count($row) !== 2 || count($rows) >= 500) {
                    throw ValidationException::withMessages(['file' => 'CSV maksimal 500 baris dengan dua kolom.']);
                }
                $data = ['name' => trim($row[0]), 'email' => trim($row[1])];
                $key = mb_strtolower($data['email']);
                if (isset($emails[$key])) {
                    throw ValidationException::withMessages(['file' => 'Email duplikat di CSV.']);
                }
                $emails[$key] = true;
                $rows[] = $data;
            }
            if (! $rows) {
                throw ValidationException::withMessages(['file' => 'CSV tidak memiliki data.']);
            }
            foreach ($rows as $index => $data) {
                $this->validateCsvRow($data, $index);
            }

            return $rows;
        } finally {
            fclose($stream);
        }
    }

    public function store(Request $request)
    {
        $data = $this->accountData($request);
        User::create([...$data, 'role' => 'mahasiswa', 'password' => Str::random(40)]);

        return redirect()->back()->with('success', 'Akun mahasiswa dibuat. Aktivasi akses memerlukan provisioning terpisah.');
    }

    public function update(Request $request, $id)
    {
        $student = User::where('role', 'mahasiswa')->findOrFail($id);
        $data = $this->accountData($request, $student->id);
        if ($student->email !== $data['email']) {
            $student->email_verified_at = null;
        }
        $student->fill($data)->save();

        return redirect()->back()->with('success', 'Data akun mahasiswa diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $student = User::where('role', 'mahasiswa')->findOrFail($id);
        // Academic rows cascade on deletion: keep this API account-only.
        foreach (['pengajuan_judul', 'pendaftaran_sempro', 'pendaftaran_sidang', 'bimbingan', 'penjadwalan'] as $table) {
            if (DB::table($table)->where('mahasiswa_id', $student->id)->exists()) {
                throw ValidationException::withMessages(['student' => 'Akun dengan riwayat akademik tidak dapat dihapus.']);
            }
        }
        $student->delete();

        return redirect()->back()->with('success', 'Akun mahasiswa dihapus.');
    }

    private function accountData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'role' => ['sometimes', Rule::in(['mahasiswa'])],
            'username' => ['prohibited'],
            'nim_nip' => ['prohibited'],
            'prodi' => ['prohibited'],
            'password' => ['prohibited'],
        ]);
    }

    public function resetPassword(Request $request, $id)
    {
        $student = User::where('role', 'mahasiswa')->findOrFail($id);

        // Preserve the existing username-based default; username is not necessarily NIM.
        $defaultPassword = $student->username;
        if (! is_string($defaultPassword) || trim($defaultPassword) === '') {
            throw ValidationException::withMessages([
                'username' => 'Username akun mahasiswa belum tersedia. Reset password tidak dapat dilakukan.',
            ]);
        }
        $student->update([
            'password' => bcrypt($defaultPassword),
        ]);

        return redirect()->back()->with(
            'success',
            'Password akun mahasiswa berhasil direset.'
        );
    }
}

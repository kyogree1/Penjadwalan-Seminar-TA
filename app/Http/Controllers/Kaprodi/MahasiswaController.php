<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StudentCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->where(function ($q) {
                $q->whereNull('prodi')
                    ->orWhere('prodi', 'like', '%Informatika%');
            })
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'email', 'username', 'nim_nip', 'no_telpon', 'prodi', 'angkatan', 'jurusan', 'status_ta'])
            ->map(function (User $user) {
                $cleanNim = preg_replace('/^NIM:\s*/i', '', $user->nim_nip ?? $user->username ?? '');

                // Fallback angkatan calculation if empty
                $angkatan = $user->angkatan;
                if (! $angkatan && strlen($cleanNim) >= 4) {
                    $angkatan = '20' . substr($cleanNim, 2, 2);
                }

                return [
                    'id' => $user->id,
                    'nama' => $user->name,
                    'nim' => $cleanNim ?: '—',
                    'angkatan' => $angkatan ?: '2023',
                    'jurusan' => $user->jurusan ?: 'Teknik Elektro, Informatika, dan Bisnis',
                    'prodi' => $user->prodi ?: 'Informatika',
                    'email' => $user->email,
                    'noTelpon' => $user->no_telpon ?: '—',
                    'statusTa' => $user->status_ta ?: 'Aktif TA',
                ];
            });

        return Inertia::render('Kaprodi/Mahasiswa', [
            'mahasiswaList' => $students,
        ]);
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, StudentCsv::HEADER_ID, ',', '"', '', "\r\n");
            fputcsv($stream, [
                'Budi Santoso',
                '11231099',
                '2023',
                'Teknik Elektro, Informatika, dan Bisnis',
                'Informatika',
                '11231099@student.itk.ac.id',
                '081234567890',
                'Aktif TA',
            ], ',', '"', '', "\r\n");
            fclose($stream);
        }, 'template-mahasiswa.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function previewImport(Request $request, StudentCsv $csv)
    {
        $request->validate(['file' => ['required', 'file', 'max:1024', 'extensions:csv']]);

        return response()->json($csv->preview($request->file('file')));
    }

    public function import(Request $request, StudentCsv $csv)
    {
        $request->validate(['file' => ['required', 'file', 'max:1024', 'extensions:csv']]);

        DB::transaction(function () use ($request, $csv) {
            $result = $csv->preview($request->file('file'));
            if (! $result['valid']) {
                throw ValidationException::withMessages(['file' => 'CSV ditolak. Periksa preview kesalahan.']);
            }

            foreach ($result['rows'] as $row) {
                $data = $row['data'];
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'username' => $data['nim'],
                    'nim_nip' => $data['nim'],
                    'no_telpon' => $data['no_telpon'] ?? null,
                    'angkatan' => $data['angkatan'],
                    'jurusan' => $data['jurusan'],
                    'prodi' => $data['prodi'],
                    'status_ta' => $data['status_ta'] ?: 'Aktif TA',
                    'role' => 'mahasiswa',
                    'password' => bcrypt(Str::random(40)),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Data mahasiswa dari berkas CSV berhasil diimpor!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', Rule::unique('users', 'username'), Rule::unique('users', 'nim_nip')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'noTelpon' => ['required', 'string', 'max:50'],
            'angkatan' => ['required', 'string', 'max:10'],
            'statusTa' => ['nullable', 'string', 'max:50'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'prodi' => ['nullable', 'string', 'max:100'],
        ]);

        User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'username' => $validated['nim'],
            'nim_nip' => $validated['nim'],
            'no_telpon' => $validated['noTelpon'],
            'jurusan' => $validated['jurusan'] ?? 'Teknik Elektro, Informatika, dan Bisnis',
            'prodi' => $validated['prodi'] ?? 'Informatika',
            'angkatan' => $validated['angkatan'],
            'status_ta' => $validated['statusTa'] ?? 'Aktif TA',
            'role' => 'mahasiswa',
            'password' => bcrypt('password'),
        ]);

        return redirect()->back()->with('success', 'Mahasiswa baru "' . $validated['nama'] . '" berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $student = User::where('role', 'mahasiswa')->findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nim' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($student->id), Rule::unique('users', 'nim_nip')->ignore($student->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->id)],
            'noTelpon' => ['required', 'string', 'max:50'],
            'angkatan' => ['required', 'string', 'max:10'],
            'statusTa' => ['nullable', 'string', 'max:50'],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'prodi' => ['nullable', 'string', 'max:100'],
        ]);

        $student->update([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'username' => $validated['nim'],
            'nim_nip' => $validated['nim'],
            'no_telpon' => $validated['noTelpon'],
            'jurusan' => $validated['jurusan'] ?? $student->jurusan ?? 'Teknik Elektro, Informatika, dan Bisnis',
            'prodi' => $validated['prodi'] ?? $student->prodi ?? 'Informatika',
            'angkatan' => $validated['angkatan'],
            'status_ta' => $validated['statusTa'] ?? 'Aktif TA',
        ]);

        return redirect()->back()->with('success', 'Data mahasiswa "' . $validated['nama'] . '" berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $student = User::where('role', 'mahasiswa')->findOrFail($id);
        $studentName = $student->name;
        $student->delete();

        return redirect()->back()->with('success', 'Data mahasiswa "' . $studentName . '" berhasil dihapus.');
    }
}

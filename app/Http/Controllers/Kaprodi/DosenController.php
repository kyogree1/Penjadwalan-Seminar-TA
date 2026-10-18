<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LecturerCsv;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DosenController extends Controller
{
    public function index(): Response
    {
        $dosenList = User::where('role', 'dosen')
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'nim_nip', 'prodi', 'jabatan'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'nip' => $user->nim_nip !== null ? preg_replace('/^NIP:\s*/i', '', $user->nim_nip) : null,
                'prodi' => $user->prodi,
                'jabatan' => $user->jabatan,
            ]);

        return Inertia::render('Kaprodi/Dosen', [
            'dosenList' => $dosenList,
        ]);
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, LecturerCsv::HEADER, ',', '"', '', "\r\n");
            fclose($stream);
        }, 'template-dosen.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function previewImport(Request $request, LecturerCsv $csv)
    {
        $request->validate(['file' => ['required', 'file', 'max:1024', 'extensions:csv']]);

        return response()->json($csv->preview($request->file('file')));
    }

    public function import(Request $request, LecturerCsv $csv)
    {
        $request->validate(['file' => ['required', 'file', 'max:1024', 'extensions:csv']]);
        DB::transaction(function () use ($request, $csv) {
            $result = $csv->preview($request->file('file'));
            if (! $result['valid']) {
                throw ValidationException::withMessages(['file' => 'CSV ditolak. Periksa preview; tidak ada baris disimpan.']);
            }
            foreach ($result['rows'] as $row) {
                $data = $row['data'];
                User::create([
                    'name' => $data['name'], 'email' => $data['email'],
                    'username' => $data['nip'], 'nim_nip' => $data['nip'],
                    'prodi' => $data['prodi'], 'jabatan' => $data['jabatan'] ?: null,
                    'role' => 'dosen', 'password' => bcrypt(Str::random(40)),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Import CSV dosen berhasil disimpan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['required', 'string', 'max:50', Rule::unique('users', 'username'), Rule::unique('users', 'nim_nip')],
            'prodi' => ['required', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['nip'],
            'nim_nip' => $validated['nip'],
            'prodi' => $validated['prodi'],
            'jabatan' => $validated['jabatan'] ?? null,
            'role' => 'dosen',
            'password' => bcrypt(Str::random(40)),
        ]);

        return redirect()->back()->with('success', 'Data dosen baru berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'nip' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id), Rule::unique('users', 'nim_nip')->ignore($user->id)],
            'prodi' => ['required', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['nip'],
            'nim_nip' => $validated['nip'],
            'prodi' => $validated['prodi'],
            'jabatan' => $validated['jabatan'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Data dosen berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Data dosen berhasil dihapus.');
    }
}

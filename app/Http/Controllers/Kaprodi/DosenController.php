<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DosenController extends Controller
{
    public function index(): Response
    {
        $dosenList = User::where('role', 'dosen')
            ->select('id', 'name', 'email', 'username', 'nim_nip', 'prodi', 'jabatan')
            ->get();

        return Inertia::render('Kaprodi/Dosen', [
            'dbDosenList' => $dosenList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'nip' => ['required', 'string', 'max:50'],
            'prodi' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['nip'],
            'nim_nip' => $validated['nip'],
            'prodi' => $validated['prodi'] ?? 'Informatika',
            'jabatan' => $validated['jabatan'] ?? 'Dosen',
            'role' => 'dosen',
            'password' => bcrypt('password123'),
        ]);

        return redirect()->back()->with('success', 'Data dosen baru berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'prodi' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
        ]);

        $user->update($validated);

        return redirect()->back()->with('success', 'Data dosen berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::where('role', 'dosen')->findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'Data dosen berhasil dihapus.');
    }
}

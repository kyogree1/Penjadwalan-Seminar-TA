<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RuanganController extends Controller
{
    public function index(): Response
    {
        $ruanganList = Ruangan::all();

        return Inertia::render('Tendik/Ruangan', [
            'dbRuanganList' => $ruanganList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ruangan' => ['required', 'string', 'max:255'],
            'gedung' => ['nullable', 'string', 'max:100'],
            'lantai' => ['nullable', 'string', 'max:50'],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:tersedia,tidak_tersedia'],
            'keterangan' => ['nullable', 'string'],
        ]);

        Ruangan::create($validated);

        return redirect()->back()->with('success', 'Ruangan sidang baru berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $validated = $request->validate([
            'nama_ruangan' => ['required', 'string', 'max:255'],
            'gedung' => ['nullable', 'string', 'max:100'],
            'lantai' => ['nullable', 'string', 'max:50'],
            'kapasitas' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:tersedia,tidak_tersedia'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $ruangan->update($validated);

        return redirect()->back()->with('success', 'Data ruangan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->delete();

        return redirect()->back()->with('success', 'Ruangan berhasil dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Tendik;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function resetPassword(Request $request, $id)
    {
        $student = User::where('role', 'mahasiswa')->findOrFail($id);

        // Reset password ke default NIM
        $defaultPassword = $student->username;
        $student->update([
            'password' => bcrypt($defaultPassword),
        ]);

        return redirect()->back()->with(
            'success',
            "Password akun untuk {$student->name} berhasil direset ke default NIM ({$defaultPassword})!"
        );
    }
}

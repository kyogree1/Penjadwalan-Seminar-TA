<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TendikFacilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_facilities_routes_enforce_authentication_and_tendik_role(): void
    {
        foreach (['/tendik/dashboard', '/tendik/arsip'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        foreach (['mahasiswa', 'dosen', 'kaprodi', 'koordinator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['/tendik/dashboard', '/tendik/arsip'] as $path) {
                $this->get($path)->assertRedirect()->assertSessionHas('error');
            }
        }
    }

    public function test_dashboard_counts_persisted_waiting_records_without_month_or_occupancy_claims(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $expected = ['antreanVerifikasi' => 0, 'antreanSempro' => 0, 'antreanSidang' => 0, 'totalMahasiswa' => 0, 'totalTerjadwal' => 0];
        $this->get('/tendik/dashboard')->assertInertia(fn (Assert $page) => $page->component('Tendik/Dashboard')->where('stats', $expected));
        $student = User::factory()->create(['role' => 'mahasiswa']);
        User::factory()->create(['role' => 'dosen']);
        foreach (['menunggu', 'menunggu', 'verifikasi_tendik', 'terjadwal', 'selesai', 'ditolak'] as $status) {
            PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'TA', 'bentuk_ta' => 'Skripsi', 'status' => $status]);
        }
        foreach (['menunggu', 'verifikasi_tendik', 'terjadwal', 'selesai', 'ditolak'] as $status) {
            PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'TA', 'status' => $status]);
        }
        $expected = ['antreanVerifikasi' => 3, 'antreanSempro' => 2, 'antreanSidang' => 1, 'totalMahasiswa' => 1, 'totalTerjadwal' => 0];
        $this->get('/tendik/dashboard')->assertInertia(fn (Assert $page) => $page->where('stats', $expected));
    }

    public function test_archive_explicitly_reports_unavailable_persistence_and_printing(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']))->get('/tendik/arsip')
            ->assertInertia(fn (Assert $page) => $page->component('Tendik/Arsip')
                ->where('archiveCapabilities', ['persistence' => true, 'printing' => false]));
    }
}

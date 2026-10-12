<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TendikFacilitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_rooms_expose_ordered_supported_fields_and_empty_results(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']))
            ->get('/tendik/ruangan')->assertInertia(fn (Assert $page) => $page->component('Tendik/Ruangan')->where('dbRuanganList', []));
        Ruangan::create(['nama_ruangan' => 'Z Room', 'kapasitas' => 30, 'status' => 'tersedia']);
        $room = Ruangan::create(['nama_ruangan' => 'A Room', 'kapasitas' => 12, 'status' => 'tidak_tersedia', 'keterangan' => 'Perawatan']);
        $this->get('/tendik/ruangan')->assertInertia(fn (Assert $page) => $page->has('dbRuanganList', 2)
            ->where('dbRuanganList.0.id', $room->id)->where('dbRuanganList.0.nama_ruangan', 'A Room')
            ->where('dbRuanganList.0.gedung', null)->where('dbRuanganList.0.lantai', null)
            ->where('dbRuanganList.0.kapasitas', 12)->where('dbRuanganList.0.status', 'tidak_tersedia')
            ->where('dbRuanganList.0.keterangan', 'Perawatan')->missing('dbRuanganList.0.fasilitas')->missing('dbRuanganList.0.sesiHariIni'));
    }

    public function test_crud_persists_validated_fields_and_rejects_invalid_payloads_without_mutations(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $payload = ['nama_ruangan' => 'Actual Room', 'gedung' => 'GKT', 'lantai' => '2', 'kapasitas' => 20, 'status' => 'tersedia', 'keterangan' => 'Catatan'];
        $this->from('/tendik/ruangan')->post('/tendik/ruangan', $payload)->assertRedirect('/tendik/ruangan')->assertSessionHas('success');
        $room = Ruangan::sole();
        $this->assertDatabaseHas('ruangan', $payload);
        foreach (['kapasitas' => 0, 'status' => 'Digunakan', 'nama_ruangan' => '', 'gedung' => str_repeat('x', 101), 'lantai' => str_repeat('x', 51)] as $field => $value) {
            $invalid = array_replace($payload, [$field => $value]);
            $this->post('/tendik/ruangan', $invalid)->assertSessionHasErrors($field);
            $this->put('/tendik/ruangan/'.$room->id, $invalid)->assertSessionHasErrors($field);
            $this->assertDatabaseCount('ruangan', 1);
            $this->assertDatabaseHas('ruangan', $payload);
        }
        $updated = array_replace($payload, ['nama_ruangan' => 'Updated', 'gedung' => null, 'lantai' => null, 'keterangan' => null, 'status' => 'tidak_tersedia']);
        $this->put('/tendik/ruangan/'.$room->id, $updated)->assertSessionHas('success');
        $this->assertDatabaseHas('ruangan', $updated);
        $this->put('/tendik/ruangan/999999', $payload)->assertNotFound();
        $this->delete('/tendik/ruangan/999999')->assertNotFound();
        $this->delete('/tendik/ruangan/'.$room->id)->assertSessionHas('success');
        $this->assertDatabaseCount('ruangan', 0);
        $this->get('/tendik/ruangan')->assertInertia(fn (Assert $page) => $page->where('dbRuanganList', []));
    }

    public function test_facilities_routes_enforce_authentication_and_tendik_role(): void
    {
        $room = Ruangan::create(['nama_ruangan' => 'Protected', 'kapasitas' => 10, 'status' => 'tersedia']);
        foreach (['/tendik/ruangan', '/tendik/dashboard', '/tendik/arsip'] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
        $this->post('/tendik/ruangan', [])->assertRedirect('/login');
        $this->put('/tendik/ruangan/'.$room->id, [])->assertRedirect('/login');
        $this->delete('/tendik/ruangan/'.$room->id)->assertRedirect('/login');
        foreach (['mahasiswa', 'dosen', 'kaprodi', 'koordinator'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            foreach (['/tendik/ruangan', '/tendik/dashboard', '/tendik/arsip'] as $path) {
                $this->get($path)->assertRedirect()->assertSessionHas('error');
            }
            $this->post('/tendik/ruangan', [])->assertRedirect()->assertSessionHas('error');
            $this->put('/tendik/ruangan/'.$room->id, [])->assertRedirect()->assertSessionHas('error');
            $this->delete('/tendik/ruangan/'.$room->id)->assertRedirect()->assertSessionHas('error');
        }
        $this->assertDatabaseCount('ruangan', 1);
    }

    public function test_dashboard_counts_persisted_waiting_records_without_month_or_occupancy_claims(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']));
        $expected = ['antreanVerifikasi' => 0, 'antreanSempro' => 0, 'antreanSidang' => 0, 'totalRuangan' => 0, 'ruanganTersedia' => 0, 'totalMahasiswa' => 0];
        $this->get('/tendik/dashboard')->assertInertia(fn (Assert $page) => $page->component('Tendik/Dashboard')->where('stats', $expected));
        $student = User::factory()->create(['role' => 'mahasiswa']);
        User::factory()->create(['role' => 'dosen']);
        foreach (['menunggu', 'menunggu', 'verifikasi_tendik', 'terjadwal', 'selesai', 'ditolak'] as $status) {
            PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'TA', 'bentuk_ta' => 'Skripsi', 'status' => $status]);
        }
        foreach (['menunggu', 'verifikasi_tendik', 'terjadwal', 'selesai', 'ditolak'] as $status) {
            PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'TA', 'status' => $status]);
        }
        foreach (['tersedia', 'tidak_tersedia'] as $status) {
            Ruangan::create(['nama_ruangan' => $status, 'kapasitas' => 10, 'status' => $status]);
        }
        $expected = ['antreanVerifikasi' => 3, 'antreanSempro' => 2, 'antreanSidang' => 1, 'totalRuangan' => 2, 'ruanganTersedia' => 1, 'totalMahasiswa' => 1];
        $this->get('/tendik/dashboard')->assertInertia(fn (Assert $page) => $page->where('stats', $expected));
    }

    public function test_archive_explicitly_reports_unavailable_persistence_and_printing(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'tendik']))->get('/tendik/arsip')
            ->assertInertia(fn (Assert $page) => $page->component('Tendik/Arsip')
                ->where('archiveCapabilities', ['persistence' => false, 'printing' => false])
                ->missing('arsip')->missing('cetak'));
    }
}

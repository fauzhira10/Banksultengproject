<?php

namespace Tests\Feature;

use App\Filament\Resources\Tikets\Pages\CreateTiket;
use App\Filament\Resources\Tikets\Pages\ListTikets;
use App\Models\Cabang;
use App\Models\Terminal;
use App\Models\Tiket;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TiketFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_create_tiket_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/tikets/create');

        $response->assertSuccessful();
        $response->assertSee('Input Tiket Masalah ATM Baru');
        $response->assertSee('Mesin ATM / CRM Terkendala');
    }

    public function test_user_can_fill_and_create_tiket(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'KCU Palu',
            'label_cabang' => '001-KCU Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU']);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'denom' => '100',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user);

        Livewire::test(CreateTiket::class)
            ->assertSuccessful()
            ->fillForm([
                'terminal_id' => $terminal->id,
                'kategori_problem' => 'Mesin ATM',
                'permasalahan' => 'DISPENSER ERROR',
                'deskripsi' => 'Uang macet di modul dispenser',
                'status' => 'Open',
                'mulai' => now()->subHours(2)->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tikets', [
            'terminal_id' => $terminal->id,
            'cabang_id' => $cabang->id,
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'status' => 'Open',
            'profil' => 'WCR.KCU1',
            'tipe_mesin' => 'Wincor 280',
        ]);
    }

    public function test_ticket_duration_is_calculated_accurately(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'KCU Palu',
            'label_cabang' => '001-KCU Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU']);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'denom' => '100',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user);

        Livewire::test(CreateTiket::class)
            ->assertSuccessful()
            ->fillForm([
                'terminal_id' => $terminal->id,
                'kategori_problem' => 'Mesin ATM',
                'permasalahan' => 'DISPENSER ERROR',
                'mulai' => '2026-09-01 10:00:00',
                'selesai' => '2026-09-11 22:00:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tikets', [
            'terminal_id' => $terminal->id,
            'status' => 'Closed',
            'durasi_lengkap' => '10 hari 12 jam 0 menit 0 detik',
            'durasi_jam_menit' => '252:00',
            'durasi_menit' => 15120,
        ]);
    }

    public function test_list_tikets_displays_aksi_column_and_button_actions(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'KCU Palu',
            'label_cabang' => '001-KCU Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU']);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'denom' => '100',
            'status' => 'Aktif',
        ]);

        $tiket = Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => 'TKT-TEST-001',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'mulai' => now()->subHour(),
            'status' => 'Open',
        ]);

        $tiketClosed = Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => 'TKT-TEST-002',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'CARD READER ERROR',
            'mulai' => now()->subHours(2),
            'selesai' => now()->subHour(),
            'status' => 'Closed',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(ListTikets::class);

        $test->assertSuccessful();
        $test->assertSee('Aksi');
        $test->assertSee('Tutup Tiket');
        $test->assertSee('Lihat');
        $test->assertSee('Ubah');
        $test->assertSee('Hapus');

        $html = $test->html();
        $this->assertStringContainsString('fi-btn', $html);
        $this->assertStringContainsString('fi-align-center', $html);
        $this->assertStringContainsString('invisible pointer-events-none', $html);
    }

    public function test_list_tikets_highlights_only_open_ticket_rows(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'KCU Palu',
            'label_cabang' => '001-KCU Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU']);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'denom' => '100',
            'status' => 'Aktif',
        ]);

        Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => 'TKT-TEST-001',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'mulai' => now()->subHour(),
            'status' => 'Open',
        ]);

        Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => 'TKT-TEST-002',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'CARD READER ERROR',
            'mulai' => now()->subHours(2),
            'selesai' => now()->subHour(),
            'status' => 'Closed',
        ]);

        $this->actingAs($user);

        $html = Livewire::test(ListTikets::class)
            ->assertSuccessful()
            ->html();

        $this->assertSame(1, substr_count($html, 'bs-tiket-row-open'));
    }
}

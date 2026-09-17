<?php

namespace Tests\Feature;

use App\Filament\Resources\Tikets\Pages\CreateTiket;
use App\Filament\Resources\Tikets\Pages\EditTiket;
use App\Filament\Resources\Tikets\Pages\ListTikets;
use App\Filament\Resources\Tikets\TiketResource;
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
        $test->assertSee('Ubah');
        $test->assertSee('Hapus');
        $test->assertDontSee('Lihat');

        $html = $test->html();
        $this->assertStringContainsString('fi-btn', $html);
        $this->assertStringContainsString('fi-align-center', $html);
        $this->assertStringContainsString('invisible pointer-events-none', $html);
        $this->assertStringContainsString(TiketResource::getUrl('view', ['record' => $tiket]), $html);
    }

    public function test_view_tiket_page_shows_back_button_to_list(): void
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

        $response = $this->actingAs($user)->get(TiketResource::getUrl('view', ['record' => $tiket]));

        $response->assertSuccessful();
        $response->assertSee('Kembali ke Daftar Tiket');
        $response->assertSee('href="'.TiketResource::getUrl('index').'"', false);
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

    public function test_generate_nomor_tiket_skips_soft_deleted_tickets(): void
    {
        $prefix = 'BST'.date('ymd');

        // Create a ticket and soft-delete it
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'KCU Palu',
            'label_cabang' => '001-KCU Palu',
        ]);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat',
            'cabang_id' => $cabang->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'status' => 'Aktif',
        ]);

        $tiket1 = Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => $prefix.'0001',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'mulai' => now(),
            'status' => 'Open',
        ]);

        $tiket1->delete(); // Soft deleted

        $nextNumber = Tiket::generateNomorTiket();
        $this->assertSame($prefix.'0002', $nextNumber);
    }

    public function test_user_can_create_ticket_when_previous_ticket_was_soft_deleted(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'KCU Palu',
            'label_cabang' => '001-KCU Palu',
        ]);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat',
            'cabang_id' => $cabang->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'status' => 'Aktif',
        ]);

        $prefix = 'BST'.date('ymd');

        $deletedTiket = Tiket::create([
            'terminal_id' => $terminal->id,
            'nomor_tiket' => $prefix.'0001',
            'kategori_problem' => 'Mesin ATM',
            'permasalahan' => 'DISPENSER ERROR',
            'mulai' => now()->subHour(),
            'status' => 'Open',
        ]);
        $deletedTiket->delete(); // Soft-deleted

        $this->actingAs($user);

        Livewire::test(CreateTiket::class)
            ->assertSuccessful()
            ->fillForm([
                'terminal_id' => $terminal->id,
                'kategori_problem' => 'Mesin ATM',
                'permasalahan' => 'SOFTWARE CORRUPT',
                'deskripsi' => 'Uji coba kendala baru',
                'status' => 'Open',
                'mulai' => now()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tikets', [
            'nomor_tiket' => $prefix.'0002',
            'permasalahan' => 'SOFTWARE CORRUPT',
            'deleted_at' => null,
        ]);
    }

    public function test_user_can_manually_type_and_edit_nomor_tiket_on_create_and_edit(): void
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
        ]);

        $this->actingAs($user);

        // 1. Manual typing when creating new ticket
        Livewire::test(CreateTiket::class)
            ->assertSuccessful()
            ->fillForm([
                'nomor_tiket' => 'VENDOR-TKT-12345',
                'terminal_id' => $terminal->id,
                'kategori_problem' => 'Mesin ATM',
                'permasalahan' => 'DISPENSER ERROR',
                'deskripsi' => 'Kendala tiket manual',
                'status' => 'Open',
                'mulai' => now()->format('Y-m-d H:i:s'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tikets', [
            'nomor_tiket' => 'VENDOR-TKT-12345',
            'permasalahan' => 'DISPENSER ERROR',
        ]);

        $createdTiket = Tiket::where('nomor_tiket', 'VENDOR-TKT-12345')->first();
        $this->assertNotNull($createdTiket);

        // 2. Manual editing of existing ticket number
        Livewire::test(EditTiket::class, ['record' => $createdTiket->getRouteKey()])
            ->assertSuccessful()
            ->assertFormSet(['nomor_tiket' => 'VENDOR-TKT-12345'])
            ->fillForm([
                'nomor_tiket' => 'VENDOR-TKT-EDITED-99',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('tikets', [
            'id' => $createdTiket->id,
            'nomor_tiket' => 'VENDOR-TKT-EDITED-99',
        ]);
    }
}

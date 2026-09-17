<?php

namespace Tests\Feature;

use App\Filament\Resources\Terminals\Pages\CreateTerminal;
use App\Filament\Resources\Terminals\Pages\EditTerminal;
use App\Models\Cabang;
use App\Models\Terminal;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TerminalFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_create_terminal_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/terminals/create');

        $response->assertSuccessful();
        $response->assertSee('Identitas & Penempatan Fisik');
        $response->assertSee('Kode Profil ATM / CRM');
        $response->assertSee('Konfigurasi Jaringan & Switch');
        $response->assertSee('Spesifikasi Mesin & Pemeliharaan Vendor');
        $response->assertDontSee('createAnother');
        $response->assertDontSee('Buat & buat lainnya');
        $response->assertDontSee('Buat dan buat lainnya');
    }

    public function test_user_can_create_terminal_record(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);

        $this->actingAs($user);

        Livewire::test(CreateTerminal::class)
            ->assertSuccessful()
            ->fillForm([
                'profil' => 'WCR.KCU2',
                'kategori' => 'ATM',
                'nama_lokasi' => 'Galeri ATM Kantor Pusat Lt. 1',
                'cabang_id' => $cabang->id,
                'urutan_cabang' => 2,
                'tipe_mesin' => 'Wincor 280',
                'serial_number' => 'WN-88192',
                'denom' => '100',
                'vendor_id' => $vendor->id,
                'ip_address' => '10.10.10.35',
                'luno' => '0205',
                'port' => '8191',
                'is_hibah' => false,
                'keterangan' => 'Unit tambahan depan customer service',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('terminals', [
            'profil' => 'WCR.KCU2',
            'nama_lokasi' => 'Galeri ATM Kantor Pusat Lt. 1',
            'ip_address' => '10.10.10.35',
            'luno' => '0205',
        ]);
    }

    public function test_user_can_access_and_edit_terminal_page(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'RS Undata Palu',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'denom' => '100',
        ]);

        $this->actingAs($user);

        $response = $this->get("/admin/terminals/{$terminal->id}/edit");
        $response->assertSuccessful();
        $response->assertSee('WCR.KCU1');
        $response->assertSee('RS Undata Palu');

        Livewire::test(EditTerminal::class, ['record' => $terminal->getRouteKey()])
            ->assertSuccessful()
            ->fillForm([
                'nama_lokasi' => 'RS Undata Palu - Gedung Rawat Inap',
                'ip_address' => '10.10.10.40',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('terminals', [
            'id' => $terminal->id,
            'nama_lokasi' => 'RS Undata Palu - Gedung Rawat Inap',
            'ip_address' => '10.10.10.40',
        ]);
    }

    public function test_user_can_access_view_terminal_page_and_see_back_button(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);
        $terminal = Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'RS Undata Palu',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Wincor 280',
            'denom' => '100',
        ]);

        $this->actingAs($user);

        $response = $this->get("/admin/terminals/{$terminal->id}");
        $response->assertSuccessful();
        $response->assertSee('Rincian Terminal ATM & CRM');
        $response->assertSee('Kembali ke Daftar Terminal');
        $response->assertSee('WCR.KCU1');
        $response->assertSee('RS Undata Palu');
    }

    public function test_hibah_terminal_automatically_assigns_vendor_koperasi_bank_sulteng(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $otherVendor = Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);

        // 1. Eloquent saving hook test
        $terminal = Terminal::create([
            'profil' => 'DBL.TEST_HIBAH',
            'nama_lokasi' => 'Lokasi Hibah Test',
            'cabang_id' => $cabang->id,
            'vendor_id' => $otherVendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'Diebold 522',
            'denom' => '100',
            'is_hibah' => true,
        ]);

        $this->assertSame('KOPERASI BANK SULTENG', $terminal->fresh()->vendor_text);
        $koperasi = Vendor::where('nama_vendor', 'KOPERASI BANK SULTENG')->first();
        $this->assertNotNull($koperasi);
        $this->assertSame($koperasi->id, $terminal->fresh()->vendor_id);

        // 2. Filament Form create test
        $this->actingAs($user);

        Livewire::test(CreateTerminal::class)
            ->assertSuccessful()
            ->fillForm([
                'profil' => 'DBL.FORM_HIBAH',
                'kategori' => 'ATM',
                'nama_lokasi' => 'Kantor Samsat 2',
                'cabang_id' => $cabang->id,
                'urutan_cabang' => 5,
                'tipe_mesin' => 'Diebold 522',
                'denom' => '100',
                'is_hibah' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $savedFormTerminal = Terminal::where('profil', 'DBL.FORM_HIBAH')->first();
        $this->assertNotNull($savedFormTerminal);
        $this->assertTrue($savedFormTerminal->is_hibah);
        $this->assertSame($koperasi->id, $savedFormTerminal->vendor_id);
        $this->assertSame('KOPERASI BANK SULTENG', $savedFormTerminal->vendor_text);

        // 3. Saving with raw vendor 'HIBAH' converts to KOPERASI BANK SULTENG
        $rawHibahTerminal = Terminal::create([
            'profil' => 'DBL.RAW_HIBAH',
            'nama_lokasi' => 'Lokasi Hibah Raw',
            'cabang_id' => $cabang->id,
            'vendor_text' => 'HIBAH',
            'kategori' => 'ATM',
            'denom' => '100',
        ]);

        $this->assertTrue($rawHibahTerminal->fresh()->is_hibah);
        $this->assertSame('KOPERASI BANK SULTENG', $rawHibahTerminal->fresh()->vendor_text);
        $this->assertSame($koperasi->id, $rawHibahTerminal->fresh()->vendor_id);
    }
}

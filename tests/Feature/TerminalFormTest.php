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
}

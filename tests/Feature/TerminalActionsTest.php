<?php

namespace Tests\Feature;

use App\Filament\Resources\Cabangs\Pages\ListCabangs;
use App\Filament\Resources\Terminals\Pages\ListTerminals;
use App\Filament\Resources\Vendors\Pages\ListVendors;
use App\Models\Cabang;
use App\Models\Terminal;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TerminalActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_terminal_table_displays_aksi_column_with_lihat_and_ubah_buttons(): void
    {
        $user = User::factory()->create();
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);
        Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'RS Undata Palu',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'kategori' => 'ATM',
            'tipe_mesin' => 'ATM WINCOR Pro Cash 480N',
            'status' => 'Aktif',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(ListTerminals::class);

        $test->assertSuccessful();
        $test->assertSee('Aksi');
        $test->assertSee('Lihat');
        $test->assertSee('Ubah');

        $html = $test->html();
        $this->assertStringContainsString('fi-btn', $html);
        $this->assertStringContainsString('fi-align-center', $html);
    }

    public function test_cabang_table_displays_aksi_column_with_buttons(): void
    {
        $user = User::factory()->create();
        Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);

        $this->actingAs($user);

        $test = Livewire::test(ListCabangs::class);

        $test->assertSuccessful();
        $test->assertSee('Aksi');
        $test->assertSee('Lihat');
        $test->assertSee('Ubah');

        $html = $test->html();
        $this->assertStringContainsString('fi-btn', $html);
        $this->assertStringContainsString('fi-align-center', $html);
    }

    public function test_vendor_table_displays_aksi_column_with_buttons(): void
    {
        $user = User::factory()->create();
        Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);

        $this->actingAs($user);

        $test = Livewire::test(ListVendors::class);

        $test->assertSuccessful();
        $test->assertSee('Aksi');
        $test->assertSee('Lihat');
        $test->assertSee('Ubah');

        $html = $test->html();
        $this->assertStringContainsString('fi-btn', $html);
        $this->assertStringContainsString('fi-align-center', $html);
    }
}

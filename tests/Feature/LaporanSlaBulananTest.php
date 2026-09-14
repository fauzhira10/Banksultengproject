<?php

namespace Tests\Feature;

use App\Filament\Pages\LaporanSlaBulanan;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LaporanSlaBulananTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/laporan-sla-bulanan');

        $response->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_can_access_sla_report_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/laporan-sla-bulanan');

        $response->assertSuccessful();
        $response->assertSee('Laporan Service Level Agreement (SLA)');
        $response->assertSee('Unduh Excel');
        $response->assertDontSee('Cetak Laporan');
        $response->assertDontSee('Muh. Abduh Bundung');
    }

    public function test_sla_report_livewire_component_renders_and_interacts(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create(['nama_vendor' => 'VENDOR_TEST']);

        $this->actingAs($user);

        Livewire::test(LaporanSlaBulanan::class)
            ->assertSuccessful()
            ->set('vendor_id', (string) $vendor->id)
            ->assertSet('vendor_id', (string) $vendor->id)
            ->set('bulan', '06')
            ->set('tahun', '2025')
            ->set('search', 'Lokasi Test')
            ->assertSet('search', 'Lokasi Test')
            ->call('clearSearch')
            ->assertSet('search', '')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_export_csv_alias_downloads_excel_file(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create(['nama_vendor' => 'VENDOR_TEST']);

        $this->actingAs($user);

        Livewire::test(LaporanSlaBulanan::class)
            ->set('vendor_id', (string) $vendor->id)
            ->set('bulan', '06')
            ->set('tahun', '2025')
            ->call('exportCsv')
            ->assertFileDownloaded('Laporan_SLA_VENDOR_TEST_JUNI_2025.xlsx');
    }
}

<?php

namespace Tests\Feature;

use App\Filament\Pages\LaporanSlaBulanan;
use App\Models\Terminal;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Auth\Events\Login;
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
        $response->assertDontSee('Semua Vendor');
        $response->assertDontSee('Reset Filter');
    }

    public function test_sla_report_livewire_component_renders_and_interacts(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create(['nama_vendor' => 'VENDOR_TEST']);

        $this->actingAs($user);

        Livewire::test(LaporanSlaBulanan::class)
            ->assertSuccessful()
            ->assertSet('vendor_id', (string) $vendor->id)
            ->assertDontSee('Semua Vendor')
            ->assertDontSee('Reset Filter')
            ->set('bulan', '06')
            ->set('tahun', '2025')
            ->set('search', 'Lokasi Test')
            ->assertSet('search', 'Lokasi Test')
            ->call('clearSearch')
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

    public function test_vendor_selection_persists_in_session_across_navigation_and_resets_on_login(): void
    {
        $user = User::factory()->create();
        $vendor1 = Vendor::create(['nama_vendor' => 'AAA_VENDOR']);
        $vendor2 = Vendor::create(['nama_vendor' => 'BBB_VENDOR']);

        $this->actingAs($user);

        // 1. Kunjungan pertama: otomatis ke vendor pertama
        Livewire::test(LaporanSlaBulanan::class)
            ->assertSet('vendor_id', (string) $vendor1->id)
            ->call('selectVendor', (string) $vendor2->id)
            ->assertSet('vendor_id', (string) $vendor2->id);

        $this->assertEquals((string) $vendor2->id, session('sla_selected_vendor_id'));

        // 2. Berpindah ke menu lain (misal ke halaman tiket)
        $this->get('/admin/tikets')->assertSuccessful();

        // 3. Kembali lagi ke Laporan SLA: tetap tersimpan di vendor2
        Livewire::test(LaporanSlaBulanan::class)
            ->assertSet('vendor_id', (string) $vendor2->id);

        // 4. Ketika login ulang: session direset dan kembali ke vendor pertama
        event(new Login('web', $user, false));
        $this->assertNull(session('sla_selected_vendor_id'));

        Livewire::test(LaporanSlaBulanan::class)
            ->assertSet('vendor_id', (string) $vendor1->id);
    }

    public function test_sla_report_limits_table_rows_to_100_and_paginates(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create(['nama_vendor' => 'VENDOR_PAGINATION']);

        for ($i = 1; $i <= 120; $i++) {
            Terminal::create([
                'profil' => 'ATM'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'vendor_id' => $vendor->id,
                'nama_lokasi' => 'Lokasi '.$i,
            ]);
        }

        $this->actingAs($user);

        $component = Livewire::test(LaporanSlaBulanan::class)
            ->set('vendor_id', (string) $vendor->id)
            ->assertSuccessful();

        $paginated = $component->get('paginatedRows');
        $this->assertEquals(120, $paginated->total());
        $this->assertEquals(100, count($paginated->items()));
        $this->assertEquals(2, $paginated->lastPage());
        $this->assertEquals(1, $paginated->currentPage());

        // Pindah ke halaman 2
        $component->call('nextPage');
        $paginatedPage2 = $component->get('paginatedRows');
        $this->assertEquals(2, $paginatedPage2->currentPage());
        $this->assertEquals(20, count($paginatedPage2->items()));

        // Kembali ke halaman 1
        $component->call('previousPage');
        $paginatedPage1 = $component->get('paginatedRows');
        $this->assertEquals(1, $paginatedPage1->currentPage());
    }
}

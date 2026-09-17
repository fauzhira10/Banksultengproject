<?php

namespace Tests\Feature;

use App\Filament\Resources\Terminals\Pages\ListTerminals;
use App\Models\Cabang;
use App\Models\Terminal;
use App\Models\User;
use App\Models\Vendor;
use App\Services\TerminalImportService;
use App\Services\TerminalTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class TerminalImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_terminal_import_service_imports_csv_correctly(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'SRISHINDU INFORMATIKA']);

        $csvContent = implode("\n", [
            'Profil,Cabang,Nama Lokasi,IP Add,ID/LUNO,PORT,Vendor,SN,Tipe Mesin,Pecahan,Status',
            'WCR.KCU1,001-Utama Palu,RS Undata Palu,175.12.41.2,0200,8220,SRISHINDU INFORMATIKA,56HG701702,ATM WINCOR Pro Cash 480N,100,Aktif',
            'CRM.KCU1,001,Bapenda Palu,172.16.50.126,0176,8191,ASSINDO,B2010D00482,CRM YIHUA,50/100,Aktif',
        ]);

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_csv_').'.csv';
        file_put_contents($tempCsv, $csvContent);

        $service = new TerminalImportService;
        $stats = $service->import($tempCsv, updateExisting: true, autoCreateRelations: true);

        @unlink($tempCsv);

        $this->assertSame(2, $stats['total']);
        $this->assertSame(2, $stats['created']);
        $this->assertSame(0, $stats['updated']);
        $this->assertEmpty($stats['errors']);

        // Check first terminal (WCR.KCU1)
        $wcr = Terminal::where('profil', 'WCR.KCU1')->first();
        $this->assertNotNull($wcr);
        $this->assertSame('RS Undata Palu', $wcr->nama_lokasi);
        $this->assertSame($cabang->id, $wcr->cabang_id);
        $this->assertSame($vendor->id, $wcr->vendor_id);
        $this->assertSame('175.12.41.2', $wcr->ip_address);
        $this->assertSame('0200', $wcr->luno);
        $this->assertSame('8220', $wcr->port);
        $this->assertSame('ATM', $wcr->kategori);
        $this->assertSame('100', $wcr->denom);

        // Check second terminal (CRM.KCU1) with auto-created vendor
        $crm = Terminal::where('profil', 'CRM.KCU1')->first();
        $this->assertNotNull($crm);
        $this->assertSame('Bapenda Palu', $crm->nama_lokasi);
        $this->assertSame($cabang->id, $crm->cabang_id);
        $this->assertSame('CRM', $crm->kategori);
        $this->assertSame('50/100', $crm->denom);
        $this->assertDatabaseHas('vendors', ['nama_vendor' => 'ASSINDO']);
    }

    public function test_terminal_import_service_upserts_existing_records(): void
    {
        Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);

        Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Lokasi Lama',
            'ip_address' => '10.10.10.1',
            'denom' => '100',
            'kategori' => 'ATM',
        ]);

        $csvContent = implode("\n", [
            'Profil,Lokasi,IP Address',
            'WCR.KCU1,Lokasi Baru Update,10.10.10.99',
        ]);

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_csv_').'.csv';
        file_put_contents($tempCsv, $csvContent);

        $service = new TerminalImportService;
        $stats = $service->import($tempCsv, updateExisting: true);

        @unlink($tempCsv);

        $this->assertSame(1, $stats['total']);
        $this->assertSame(0, $stats['created']);
        $this->assertSame(1, $stats['updated']);

        $updatedTerminal = Terminal::where('profil', 'WCR.KCU1')->first();
        $this->assertSame('Lokasi Baru Update', $updatedTerminal->nama_lokasi);
        $this->assertSame('10.10.10.99', $updatedTerminal->ip_address);
    }

    public function test_terminal_import_service_skips_existing_when_update_disabled(): void
    {
        Terminal::create([
            'profil' => 'WCR.KCU1',
            'nama_lokasi' => 'Lokasi Asli',
            'ip_address' => '10.10.10.1',
            'denom' => '100',
            'kategori' => 'ATM',
        ]);

        $csvContent = implode("\n", [
            'Profil,Lokasi,IP Address',
            'WCR.KCU1,Mencoba Update,10.10.10.99',
        ]);

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_csv_').'.csv';
        file_put_contents($tempCsv, $csvContent);

        $service = new TerminalImportService;
        $stats = $service->import($tempCsv, updateExisting: false);

        @unlink($tempCsv);

        $this->assertSame(1, $stats['total']);
        $this->assertSame(0, $stats['created']);
        $this->assertSame(0, $stats['updated']);
        $this->assertSame(1, $stats['skipped']);

        $terminal = Terminal::where('profil', 'WCR.KCU1')->first();
        $this->assertSame('Lokasi Asli', $terminal->nama_lokasi);
    }

    public function test_terminal_import_service_imports_xlsx_file(): void
    {
        $tempXlsx = tempnam(sys_get_temp_dir(), 'test_xlsx_').'.xlsx';

        $writer = new Writer(new Options);
        $writer->openToFile($tempXlsx);

        $writer->addRow(new Row([
            Cell::fromValue('Kode Profil'),
            Cell::fromValue('Cabang'),
            Cell::fromValue('Lokasi Fisik'),
            Cell::fromValue('IP'),
            Cell::fromValue('LUNO'),
            Cell::fromValue('Port'),
            Cell::fromValue('Vendor'),
            Cell::fromValue('SN'),
            Cell::fromValue('Tipe'),
        ]));

        $writer->addRow(new Row([
            Cell::fromValue('DBL.GBNR'),
            Cell::fromValue('001-Utama Palu'),
            Cell::fromValue('Kantor Gubernur'),
            Cell::fromValue('172.16.30.2'),
            Cell::fromValue('0002'),
            Cell::fromValue('8002'),
            Cell::fromValue('ASSINDO'),
            Cell::fromValue('1529FDC06730'),
            Cell::fromValue('ATM Diebold OPTEVA 529'),
        ]));

        $writer->close();

        $service = new TerminalImportService;
        $stats = $service->import($tempXlsx, updateExisting: true, autoCreateRelations: true);

        @unlink($tempXlsx);

        $this->assertSame(1, $stats['total']);
        $this->assertSame(1, $stats['created']);

        $terminal = Terminal::where('profil', 'DBL.GBNR')->first();
        $this->assertNotNull($terminal);
        $this->assertSame('Kantor Gubernur', $terminal->nama_lokasi);
        $this->assertSame('0002', $terminal->luno);
        $this->assertSame('1529FDC06730', $terminal->serial_number);
    }

    public function test_terminal_template_service_generates_file(): void
    {
        $service = new TerminalTemplateService;
        $response = $service->generateTemplate();

        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Data_Terminal_ATM_CRM_Bank_Sulteng.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_terminal_template_service_exports_database_records(): void
    {
        $cabang = Cabang::create([
            'kode_cabang' => '001',
            'nama_cabang' => 'Utama Palu',
            'label_cabang' => '001-Utama Palu',
        ]);
        $vendor = Vendor::create(['nama_vendor' => 'PT SRISHINDU INFORMATIKA']);

        Terminal::create([
            'profil' => 'WCR.KCU99',
            'cabang_id' => $cabang->id,
            'vendor_id' => $vendor->id,
            'nama_lokasi' => 'Galeri ATM Utama',
            'tipe_mesin' => 'Wincor 280',
            'kategori' => 'ATM',
        ]);

        $service = new TerminalTemplateService;
        $response = $service->generateTemplate();

        $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Data_Terminal_ATM_CRM_Bank_Sulteng.xlsx', (string) $response->headers->get('Content-Disposition'));
    }

    public function test_artisan_command_terminal_import(): void
    {
        $csvContent = implode("\n", [
            'Profil,Lokasi,IP Address',
            'CMD.TEST1,Lokasi Command Test,192.168.1.50',
        ]);

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_cmd_').'.csv';
        file_put_contents($tempCsv, $csvContent);

        $this->artisan('terminal:import', ['file' => $tempCsv])
            ->assertSuccessful();

        @unlink($tempCsv);

        $this->assertDatabaseHas('terminals', [
            'profil' => 'CMD.TEST1',
            'nama_lokasi' => 'Lokasi Command Test',
        ]);
    }

    public function test_list_terminals_displays_import_and_template_actions(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ListTerminals::class)
            ->assertSuccessful()
            ->assertActionExists('importExcel')
            ->assertActionExists('downloadTemplate');
    }
}

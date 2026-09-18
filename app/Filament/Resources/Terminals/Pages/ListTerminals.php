<?php

namespace App\Filament\Resources\Terminals\Pages;

use App\Filament\Resources\Terminals\TerminalResource;
use App\Filament\Resources\Terminals\Widgets\TerminalOverviewWidget;
use App\Models\Terminal;
use App\Services\TerminalImportService;
use App\Services\TerminalTemplateService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListTerminals extends ListRecords
{
    protected static string $resource = TerminalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Ekspor Data Excel')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('gray')
                ->action(fn (TerminalTemplateService $service) => $service->generateTemplate()),

            Action::make('importExcel')
                ->authorize('import')
                ->label('Import Excel')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Master Data ATM dari Excel')
                ->modalDescription('Unggah file .xlsx atau .csv berisi data ATM untuk dimasukkan langsung ke database.')
                ->modalSubmitActionLabel('Mulai Import Data')
                ->modalWidth(Width::Large)
                ->form([
                    FileUpload::make('attachment')
                        ->label('Pilih File Excel / CSV')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'text/plain',
                        ])
                        ->disk('local')
                        ->directory('temp-imports')
                        ->maxSize(5120)
                        ->required()
                        ->helperText('Format yang didukung: .xlsx, .xls, .csv. Pastikan menggunakan format kolom sesuai template.'),

                    Toggle::make('update_existing')
                        ->label('Perbarui data jika Kode Profil ATM sudah ada (Upsert)')
                        ->default(true)
                        ->helperText('Jika aktif, mesin dengan kode Profil yang sama akan diperbarui datanya.'),

                    Toggle::make('auto_create_relations')
                        ->label('Otomatis daftarkan Cabang & Vendor jika belum ada')
                        ->default(true)
                        ->helperText('Jika nama vendor atau cabang di file Excel belum terdaftar di database, sistem akan menambahkannya otomatis.'),
                ])
                ->action(function (array $data, TerminalImportService $importService): void {
                    $attachment = $data['attachment'] ?? null;
                    if (! $attachment) {
                        return;
                    }

                    $filePath = Storage::disk('local')->path($attachment);

                    if (! file_exists($filePath)) {
                        Notification::make()
                            ->title('File Tidak Ditemukan')
                            ->body('Gagal membaca file yang diunggah.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $updateExisting = (bool) ($data['update_existing'] ?? true);
                    $autoCreateRelations = (bool) ($data['auto_create_relations'] ?? true);

                    try {
                        $stats = $importService->import($filePath, $updateExisting, $autoCreateRelations);
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title('Import Gagal')
                            ->body('File tidak dapat diproses. Pastikan format file dan kolom sesuai template.')
                            ->danger()
                            ->send();

                        return;
                    } finally {
                        Storage::disk('local')->delete($attachment);
                    }

                    $summary = "Total diproses: {$stats['total']} data. (Baru: {$stats['created']}, Diperbarui: {$stats['updated']}, Dilewati: {$stats['skipped']}).";

                    if (! empty($stats['errors'])) {
                        $errorCount = count($stats['errors']);
                        $errorDetails = implode('<br>', array_slice($stats['errors'], 0, 5));
                        if ($errorCount > 5) {
                            $errorDetails .= '<br>...dan '.($errorCount - 5).' kesalahan lainnya.';
                        }

                        Notification::make()
                            ->title('Import Selesai dengan Catatan')
                            ->body("{$summary}<br><br><strong>{$errorCount} baris bermasalah:</strong><br>{$errorDetails}")
                            ->warning()
                            ->persistent()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Import Data ATM Berhasil')
                            ->body($summary)
                            ->success()
                            ->send();
                    }
                }),

            CreateAction::make()
                ->label('Tambah Terminal Baru')
                ->icon('heroicon-m-plus')
                ->slideOver(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TerminalOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        $counts = Terminal::getCountsSummary();

        return [
            'all' => Tab::make('Semua')
                ->badge($counts['total'])
                ->badgeColor('primary'),

            'crm' => Tab::make('CRM (Setor Tarik)')
                ->badge($counts['crm'])
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'CRM')),

            'atm' => Tab::make('ATM Tarik Tunai')
                ->badge($counts['atm'])
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'ATM')),

            'hibah' => Tab::make('Mesin Hibah (D522)')
                ->badge($counts['hibah'])
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_hibah', true)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        $sessionTab = session('terminals_active_tab');
        if (filled($sessionTab) && array_key_exists($sessionTab, $this->getCachedTabs())) {
            return $sessionTab;
        }

        return parent::getDefaultActiveTab();
    }

    public function updatedActiveTab(): void
    {
        parent::updatedActiveTab();

        if (filled($this->activeTab)) {
            session(['terminals_active_tab' => $this->activeTab]);
        }
    }
}

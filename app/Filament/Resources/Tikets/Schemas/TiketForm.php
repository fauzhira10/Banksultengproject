<?php

namespace App\Filament\Resources\Tikets\Schemas;

use App\Models\Terminal;
use App\Models\Tiket;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TiketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                // ==================== KOLOM KIRI (UTAMA - 2 KOLOM) ====================
                Group::make([
                    // 1. Identitas Mesin ATM Terkendala
                    Section::make('Mesin ATM / CRM Terkendala')
                        ->description('Pilih mesin ATM yang mengalami gangguan operasional')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Select::make('terminal_id')
                                ->label('Pilih Terminal ATM / CRM')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->relationship('terminal', 'profil')
                                ->getOptionLabelFromRecordUsing(fn (Terminal $record): string => "{$record->profil} — {$record->nama_lokasi} (".($record->cabang?->label_cabang ?? $record->cabang_text ?? 'Bank Sulteng').')')
                                ->searchable(['profil', 'nama_lokasi', 'luno', 'serial_number'])
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (! $state) {
                                        $set('cabang_id', null);
                                        $set('cabang_text', null);

                                        return;
                                    }
                                    $terminal = Terminal::with(['cabang', 'vendor'])->find($state);
                                    if ($terminal) {
                                        $set('cabang_id', $terminal->cabang_id);
                                        $set('cabang_text', $terminal->cabang?->label_cabang ?? $terminal->cabang_text);
                                    }
                                }),

                            Hidden::make('cabang_id'),
                            Hidden::make('cabang_text'),

                            // Kartu Rincian Terminal Interaktif (Otomatis muncul saat terminal dipilih)
                            Placeholder::make('terminal_preview')
                                ->hiddenLabel()
                                ->content(function (callable $get) {
                                    $terminalId = $get('terminal_id');
                                    if (! $terminalId) {
                                        return new HtmlString('
                                            <div class="flex items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50/60 p-3.5 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800/40 dark:text-gray-400">
                                                <div class="rounded-lg bg-gray-200/80 p-2 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-gray-800 dark:text-gray-200">Belum ada terminal yang dipilih</span>
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">Pilih terminal ATM/CRM di atas untuk melihat informasi lokasi, cabang, nomor seri, dan vendor pemeliharaan.</p>
                                                </div>
                                            </div>
                                        ');
                                    }

                                    $terminal = Terminal::with(['cabang', 'vendor'])->find($terminalId);
                                    if (! $terminal) {
                                        return null;
                                    }

                                    $cabangLabel = e($terminal->cabang?->label_cabang ?? $terminal->cabang_text ?? 'Bank Sulteng');
                                    $lokasi = e($terminal->nama_lokasi ?? '-');
                                    $luno = e($terminal->luno ?? '-');
                                    $sn = e($terminal->serial_number ?? '-');
                                    $tipe = e($terminal->tipe_mesin ?? 'ATM');
                                    $kategori = e($terminal->kategori ?? 'ATM');
                                    $vendor = e($terminal->vendor?->nama_vendor ?? $terminal->vendor_text ?? '-');
                                    $ip = e($terminal->ip_address ?? '-');

                                    return new HtmlString("
                                        <div class=\"overflow-hidden rounded-xl border border-blue-200/80 bg-blue-50/50 p-4 transition-colors dark:border-blue-900/60 dark:bg-blue-950/30\">
                                            <div class=\"flex items-center justify-between border-b border-blue-200/60 pb-2.5 dark:border-blue-900/40\">
                                                <div class=\"flex items-center gap-2\">
                                                    <span class=\"inline-flex items-center rounded-md bg-blue-600 px-2 py-0.5 text-[11px] font-bold text-white shadow-xs\">
                                                        {$kategori}
                                                    </span>
                                                    <span class=\"text-xs font-bold text-gray-900 dark:text-white\">{$terminal->profil}</span>
                                                </div>
                                                <span class=\"rounded-md bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-800 dark:bg-blue-900/50 dark:text-blue-300\">
                                                    {$tipe}
                                                </span>
                                            </div>

                                            <div class=\"mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4 text-xs\">
                                                <div>
                                                    <span class=\"text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400\">Cabang Pengelola</span>
                                                    <div class=\"mt-0.5 font-bold text-gray-900 dark:text-gray-100\">{$cabangLabel}</div>
                                                </div>
                                                <div>
                                                    <span class=\"text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400\">Lokasi Fisik</span>
                                                    <div class=\"mt-0.5 font-bold text-gray-900 dark:text-gray-100\">{$lokasi}</div>
                                                </div>
                                                <div>
                                                    <span class=\"text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400\">LUNO & Serial</span>
                                                    <div class=\"mt-0.5 font-mono text-[11px] font-bold text-gray-800 dark:text-gray-200\">{$luno} / {$sn}</div>
                                                </div>
                                                <div>
                                                    <span class=\"text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400\">Vendor & IP</span>
                                                    <div class=\"mt-0.5 font-bold text-blue-600 dark:text-blue-400\">{$vendor} <span class=\"font-mono text-[10px] text-gray-400 dark:text-gray-500\">({$ip})</span></div>
                                                </div>
                                            </div>
                                        </div>
                                    ");
                                }),
                        ]),

                    // 2. Rincian Kendala & Permasalahan
                    Section::make('Rincian Kendala & Permasalahan')
                        ->description('Identifikasi kategori masalah dan deskripsi problem sesuai standar SLA')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->schema([
                            ToggleButtons::make('kategori_problem')
                                ->label('Kategori Problem')
                                ->options([
                                    'Mesin ATM' => 'Mesin ATM',
                                    'Jaringan' => 'Jaringan',
                                    'Listrik' => 'Listrik',
                                    'System' => 'System',
                                ])
                                ->colors([
                                    'Mesin ATM' => 'primary',
                                    'Jaringan' => 'warning',
                                    'Listrik' => 'danger',
                                    'System' => 'info',
                                ])
                                ->icons([
                                    'Mesin ATM' => 'heroicon-m-cpu-chip',
                                    'Jaringan' => 'heroicon-m-signal',
                                    'Listrik' => 'heroicon-m-bolt',
                                    'System' => 'heroicon-m-computer-desktop',
                                ])
                                ->default('Mesin ATM')
                                ->required()
                                ->inline(),

                            Select::make('permasalahan')
                                ->label('Permasalahan (Problem Description)')
                                ->options([
                                    'DISPENSER ERROR' => 'DISPENSER ERROR / EROR (Dispenser Macet/Fail)',
                                    'CARD READER ERROR' => 'CARD READER ERROR (Kartu Tertelan/Gagal Baca)',
                                    'SOFTWARE CORRUPT' => 'SOFTWARE CORRUPT (Aplikasi Hang/Error)',
                                    'PRINTER ERROR' => 'PRINTER ERROR (Receipt / Journal Kertas Habis)',
                                    'USB LAN RUSAK' => 'USB LAN RUSAK (Komunikasi Terminal Terputus)',
                                    'DUDUKAN EPP PATAH' => 'DUDUKAN EPP PATAH (Pin Pad Kerusakan Fisik)',
                                    'SENSOR ERROR' => 'SENSOR ERROR (Sensor Pintu/Kaset Bermasalah)',
                                    'FASKIA PATAH' => 'FASKIA PATAH (Body / Casing Rusak)',
                                    'NETWORK OFFLINE' => 'NETWORK OFFLINE / TIMEOUT (Jaringan Terputus)',
                                    'POWER FAILURE' => 'POWER FAILURE (Mati Lampu / UPS Drop)',
                                    'CASH OUT' => 'CASH OUT (Uang Tunai di Kaset Habis)',
                                    'CASSETTE FAULT' => 'CASSETTE FAULT (Kaset Uang Tidak Terdeteksi)',
                                ])
                                ->searchable()
                                ->required()
                                ->helperText('Pilih jenis kendala standar operasional Bank Sulteng'),

                            Textarea::make('deskripsi')
                                ->label('Detail Deskripsi & Gejala Kerusakan')
                                ->placeholder('Jelaskan detail gejala kendala, kode error pada monitor, kronologi insiden, atau laporan nasabah...')
                                ->rows(3),
                        ]),

                    // 3. Kontak Pelapor & Penanganan Vendor
                    Section::make('Kontak Pelapor & Tindakan Solusi')
                        ->description('Informasi petugas pelapor di unit kerja dan catatan perbaikan teknisi')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('contact_person')
                                    ->label('Contact Person (PIC / Pelapor)')
                                    ->placeholder('Contoh: Michael / CS Cabang Salakan')
                                    ->prefixIcon('heroicon-m-user'),

                                TextInput::make('phone_number')
                                    ->label('Nomor Telepon / WhatsApp PIC')
                                    ->tel()
                                    ->placeholder('Contoh: 082196336263')
                                    ->prefixIcon('heroicon-m-phone'),
                            ]),

                            Textarea::make('tindakan')
                                ->label('Tindakan / Solusi Perbaikan Vendor')
                                ->placeholder('Catat solusi perbaikan teknisi (misal: penggantian modul dispenser, restart modem VSAT, dll)...')
                                ->rows(3)
                                ->helperText('Dapat dilengkapi saat tiket berstatus Closed / Selesai'),
                        ]),
                ])->columnSpan(['lg' => 2]),

                // ==================== KOLOM KANAN (SIDEBAR - 1 KOLOM) ====================
                Group::make([
                    // 4. Status & Identitas Tiket
                    Section::make('Status Tiket')
                        ->icon('heroicon-o-ticket')
                        ->schema([
                            TextInput::make('nomor_tiket')
                                ->label('Nomor Tiket')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->default(fn () => Tiket::generateNomorTiket())
                                ->readOnly()
                                ->extraInputAttributes(['class' => 'font-mono font-bold tracking-wider'])
                                ->helperText('Dibuat otomatis oleh sistem'),

                            ToggleButtons::make('status')
                                ->label('Status Pengerjaan')
                                ->options([
                                    'Open' => 'Open (Aktif)',
                                    'Closed' => 'Closed (Selesai)',
                                ])
                                ->colors([
                                    'Open' => 'warning',
                                    'Closed' => 'success',
                                ])
                                ->icons([
                                    'Open' => 'heroicon-m-exclamation-circle',
                                    'Closed' => 'heroicon-m-check-circle',
                                ])
                                ->default('Open')
                                ->required()
                                ->inline()
                                ->live(),
                        ]),

                    // 5. Waktu & Dampak SLA (Down Time)
                    Section::make('Waktu Gangguan & SLA')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            DateTimePicker::make('mulai')
                                ->label('Waktu Mulai (Open Tiket)')
                                ->required()
                                ->default(now())
                                ->seconds(false)
                                ->live()
                                ->afterStateUpdated(fn ($state, callable $get, callable $set) => static::calculateDuration($get, $set)),

                            DateTimePicker::make('selesai')
                                ->label('Waktu Selesai (Closed Tiket)')
                                ->seconds(false)
                                ->live()
                                ->helperText('Kosongkan bila gangguan masih berlangsung')
                                ->suffixAction(
                                    Action::make('setNow')
                                        ->label('Sekarang')
                                        ->icon('heroicon-m-clock')
                                        ->tooltip('Set waktu selesai ke waktu saat ini dan tandai Closed')
                                        ->action(function (callable $set, callable $get) {
                                            $set('selesai', now());
                                            $set('status', 'Closed');
                                            static::calculateDuration($get, $set);
                                        })
                                )
                                ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                    if ($state) {
                                        $set('status', 'Closed');
                                    }
                                    static::calculateDuration($get, $set);
                                }),

                            Hidden::make('durasi_menit'),
                            Hidden::make('durasi_jam_menit'),
                            Hidden::make('durasi_lengkap'),

                            // Kartu Live Perhitungan Down Time & Dampak SLA
                            Placeholder::make('durasi_preview')
                                ->hiddenLabel()
                                ->content(function (callable $get) {
                                    $mulai = $get('mulai');
                                    $selesai = $get('selesai');

                                    if (! $mulai) {
                                        return new HtmlString('
                                            <div class="rounded-xl border border-dashed border-gray-300 p-3 text-center text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                                                Tentukan waktu mulai untuk melihat perhitungan durasi SLA.
                                            </div>
                                        ');
                                    }

                                    try {
                                        $startTime = Carbon::parse($mulai);

                                        if ($selesai) {
                                            $endTime = Carbon::parse($selesai);
                                            if ($endTime->greaterThanOrEqualTo($startTime)) {
                                                $totalMinutes = (int) $startTime->diffInMinutes($endTime);
                                                $hours = floor($totalMinutes / 60);
                                                $mins = $totalMinutes % 60;
                                                $formattedTime = sprintf('%d Jam %02d Menit', $hours, $mins);

                                                return new HtmlString('
                                                    <div class="rounded-xl border border-emerald-200/90 bg-emerald-50/60 p-4 transition-colors dark:border-emerald-900/60 dark:bg-emerald-950/30">
                                                        <div class="flex items-center justify-between text-xs">
                                                            <span class="font-semibold text-emerald-800 dark:text-emerald-300">Total Down Time SLA</span>
                                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/60 dark:text-emerald-300">
                                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                                </svg>
                                                                Selesai
                                                            </span>
                                                        </div>
                                                        <div class="mt-2 flex items-baseline gap-2">
                                                            <span class="text-2xl font-black font-mono tracking-tight text-emerald-700 dark:text-emerald-400">
                                                                '.number_format($totalMinutes, 0, ',', '.')."
                                                            </span>
                                                            <span class=\"text-xs font-bold text-emerald-800 dark:text-emerald-300\">Menit</span>
                                                        </div>
                                                        <div class=\"mt-1 text-xs text-emerald-700 dark:text-emerald-400\">
                                                            Total durasi: <strong>{$formattedTime}</strong>
                                                        </div>
                                                    </div>
                                                ");
                                            }
                                        }

                                        // Tiket masih Open (sedang berjalan)
                                        $runningMinutes = max(0, (int) $startTime->diffInMinutes(now()));
                                        $hours = floor($runningMinutes / 60);
                                        $mins = $runningMinutes % 60;
                                        $runningTime = sprintf('%d Jam %02d Menit', $hours, $mins);

                                        return new HtmlString('
                                            <div class="rounded-xl border border-amber-200/90 bg-amber-50/60 p-4 transition-colors dark:border-amber-900/60 dark:bg-amber-950/30">
                                                <div class="flex items-center justify-between text-xs">
                                                    <span class="font-semibold text-amber-800 dark:text-amber-300">Tiket Sedang Berjalan</span>
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900/60 dark:text-amber-300">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                        Open
                                                    </span>
                                                </div>
                                                <div class="mt-2 flex items-baseline gap-2">
                                                    <span class="text-2xl font-black font-mono tracking-tight text-amber-700 dark:text-amber-400">
                                                        ~'.number_format($runningMinutes, 0, ',', '.')."
                                                    </span>
                                                    <span class=\"text-xs font-bold text-amber-800 dark:text-amber-300\">Menit Berjalan</span>
                                                </div>
                                                <div class=\"mt-1 text-[11px] text-amber-700 dark:text-amber-400\">
                                                    Durasi gangguan: <strong>{$runningTime}</strong>
                                                </div>
                                            </div>
                                        ");
                                    } catch (\Throwable) {
                                        return null;
                                    }
                                }),
                        ]),
                ])->columnSpan(['lg' => 1]),
            ]);
    }

    public static function calculateDuration(callable $get, callable $set): void
    {
        $mulai = $get('mulai');
        $selesai = $get('selesai');

        if (! $mulai || ! $selesai) {
            $set('durasi_menit', null);
            $set('durasi_jam_menit', null);
            $set('durasi_lengkap', null);

            return;
        }

        try {
            $start = Carbon::parse($mulai);
            $end = Carbon::parse($selesai);

            if ($end->greaterThanOrEqualTo($start)) {
                $totalMinutes = (int) $start->diffInMinutes($end);
                $totalSeconds = (int) $start->diffInSeconds($end);

                $set('durasi_menit', $totalMinutes);

                $hours = floor($totalMinutes / 60);
                $mins = $totalMinutes % 60;
                $set('durasi_jam_menit', sprintf('%d:%02d', $hours, $mins));

                $days = floor($totalMinutes / 1440);
                $remHours = floor(($totalMinutes % 1440) / 60);
                $remMins = $totalMinutes % 60;
                $remSecs = $totalSeconds % 60;
                $set('durasi_lengkap', "{$days}hari{$remHours}jam{$remMins}menit{$remSecs}detik");
            }
        } catch (\Throwable) {
            // Abaikan parsing error saat user sedang mengetik
        }
    }
}

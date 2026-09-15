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
                    // 1. Data Tiket & Kendala Masalah
                    Section::make('Informasi Tiket & Permasalahan')
                        ->description('Identifikasi nomor tiket dan rincian kendala gangguan operasional')
                        ->icon('heroicon-o-exclamation-triangle')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('nomor_tiket')
                                    ->label('No Tiket')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->default(fn () => Tiket::generateNomorTiket())
                                    ->readOnly()
                                    ->extraInputAttributes(['class' => 'font-mono font-bold tracking-wider'])
                                    ->helperText('Dibuat otomatis oleh sistem'),

                                Select::make('permasalahan')
                                    ->label('Permasalahan')
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
                            ]),

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

                            Textarea::make('deskripsi')
                                ->label('Detail Deskripsi & Gejala Kerusakan (Opsional)')
                                ->placeholder('Jelaskan detail kendala, kode error pada monitor, kronologi kejadian...')
                                ->rows(2),
                        ]),

                    // 2. Data Mesin ATM (Sesuai Kolom Excel: Lokasi, ID, Profile, Type, SN)
                    Section::make('Mesin ATM / CRM Terkendala')
                        ->description('Pilih mesin ATM/CRM untuk mengisi otomatis Lokasi, ID, Profile, Type, dan SN')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Select::make('terminal_id')
                                ->label('Pilih Terminal ATM / CRM')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->relationship('terminal', 'profil', modifyQueryUsing: fn ($query) => $query->with('cabang'))
                                ->getOptionLabelFromRecordUsing(fn (Terminal $record): string => "{$record->profil} — {$record->nama_lokasi} (".($record->cabang?->label_cabang ?? $record->cabang_text ?? 'Bank Sulteng').')')
                                ->searchable(['profil', 'nama_lokasi', 'luno', 'serial_number'])
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if (! $state) {
                                        $set('cabang_id', null);
                                        $set('cabang_text', null);
                                        $set('lokasi', null);
                                        $set('atm_id', null);
                                        $set('profil', null);
                                        $set('tipe_mesin', null);
                                        $set('serial_number', null);

                                        return;
                                    }
                                    $terminal = Terminal::with(['cabang', 'vendor'])->find($state);
                                    if ($terminal) {
                                        $set('cabang_id', $terminal->cabang_id);
                                        $set('cabang_text', $terminal->cabang?->label_cabang ?? $terminal->cabang_text);
                                        $set('lokasi', $terminal->nama_lokasi);
                                        $set('atm_id', $terminal->luno);
                                        $set('profil', $terminal->profil);
                                        $set('tipe_mesin', $terminal->tipe_mesin);
                                        $set('serial_number', $terminal->serial_number);
                                    }
                                }),

                            Hidden::make('cabang_id'),
                            Hidden::make('cabang_text'),

                            // 5 Kolom Excel: Lokasi, ID, Profile, Type, SN
                            Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])->schema([
                                TextInput::make('lokasi')
                                    ->label('Lokasi')
                                    ->placeholder('Nama lokasi fisik ATM')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpan(['default' => 1, 'sm' => 2, 'lg' => 2]),

                                TextInput::make('atm_id')
                                    ->label('ID (ID Mesin / LUNO)')
                                    ->placeholder('Contoh: 0036')
                                    ->prefixIcon('heroicon-m-identification')
                                    ->columnSpan(['default' => 1, 'sm' => 1, 'lg' => 1]),

                                TextInput::make('profil')
                                    ->label('Profile')
                                    ->placeholder('Contoh: DBL.SLKN')
                                    ->prefixIcon('heroicon-m-server-stack'),

                                TextInput::make('tipe_mesin')
                                    ->label('Type')
                                    ->placeholder('Contoh: Diebold 529')
                                    ->prefixIcon('heroicon-m-tag'),

                                TextInput::make('serial_number')
                                    ->label('SN (Serial Number)')
                                    ->placeholder('Contoh: FDC - 05376')
                                    ->prefixIcon('heroicon-m-hashtag'),
                            ]),
                        ]),

                    // 3. Durasi Problem (Terbagi 3 Kolom Sesuai Kolom Excel)
                    Section::make('Durasi Problem (Otomatis)')
                        ->description('Nilai durasi di bawah ini terhitung otomatis saat waktu Start & End dimasukkan')
                        ->icon('heroicon-o-clock')
                        ->schema([
                            Grid::make(['default' => 1, 'sm' => 3])->schema([
                                TextInput::make('durasi_lengkap')
                                    ->label('1. Durasi Lengkap')
                                    ->placeholder('Contoh: 10 hari 12 jam 0 menit 0 detik')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->extraInputAttributes(['class' => 'font-mono font-semibold bg-gray-50 dark:bg-gray-800'])
                                    ->helperText('Format hari, jam, menit, detik'),

                                TextInput::make('durasi_jam_menit')
                                    ->label('2. Jam:Menit')
                                    ->placeholder('Contoh: 47:55')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->extraInputAttributes(['class' => 'font-mono font-semibold bg-gray-50 dark:bg-gray-800'])
                                    ->helperText('Format total jam:menit'),

                                TextInput::make('durasi_menit')
                                    ->label('3. Total Menit')
                                    ->placeholder('Contoh: 2875')
                                    ->numeric()
                                    ->readOnly()
                                    ->dehydrated()
                                    ->extraInputAttributes(['class' => 'font-mono font-semibold bg-gray-50 dark:bg-gray-800'])
                                    ->helperText('Format angka menit'),
                            ]),
                        ]),
                ])->columnSpan(['lg' => 2]),

                // ==================== KOLOM KANAN (SIDEBAR - 1 KOLOM) ====================
                Group::make([
                    // 4. Open Closed Tiket (Start - End)
                    Section::make('Open Closed Tiket (Start - End)')
                        ->icon('heroicon-o-calendar')
                        ->schema([
                            DateTimePicker::make('mulai')
                                ->label('Open Tiket (Start)')
                                ->required()
                                ->default(now())
                                ->seconds(false)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $get, callable $set) => static::calculateDuration($get, $set)),

                            DateTimePicker::make('selesai')
                                ->label('Closed Tiket (End)')
                                ->seconds(false)
                                ->live(onBlur: true)
                                ->helperText('Kosongkan bila gangguan masih berlangsung (Open)')
                                ->suffixAction(
                                    Action::make('setNow')
                                        ->label('Sekarang')
                                        ->icon('heroicon-m-clock')
                                        ->tooltip('Set waktu selesai ke waktu saat ini dan ubah status ke Closed')
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

                            // Kartu Live Perhitungan Down Time SLA
                            Placeholder::make('durasi_preview')
                                ->hiddenLabel()
                                ->content(function (callable $get) {
                                    $mulai = $get('mulai');
                                    $selesai = $get('selesai');

                                    if (! $mulai) {
                                        return new HtmlString('
                                            <div class="rounded-xl border border-dashed border-gray-300 p-3 text-center text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                                                Tentukan waktu Open (Start) untuk memantau durasi SLA.
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
                                                                Closed
                                                            </span>
                                                        </div>
                                                        <div class="mt-2 flex items-baseline gap-2">
                                                            <span class="text-2xl font-black font-mono tracking-tight text-emerald-700 dark:text-emerald-400">
                                                                '.number_format($totalMinutes, 0, ',', '.')."
                                                            </span>
                                                            <span class=\"text-xs font-bold text-emerald-800 dark:text-emerald-300\">Menit</span>
                                                        </div>
                                                        <div class=\"mt-1 text-xs text-emerald-700 dark:text-emerald-400\">
                                                            Durasi gangguan: <strong>{$formattedTime}</strong>
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
                                                    Durasi berjalan: <strong>{$runningTime}</strong>
                                                </div>
                                            </div>
                                        ");
                                    } catch (\Throwable) {
                                        return null;
                                    }
                                }),
                        ]),

                    // 5. Status Tiket
                    Section::make('Status Operasional Tiket')
                        ->description('Status pengerjaan gangguan ATM')
                        ->icon('heroicon-o-ticket')
                        ->schema([
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

                $hours = floor($totalMinutes / 60);
                $mins = $totalMinutes % 60;

                $days = floor($totalMinutes / 1440);
                $remHours = floor(($totalMinutes % 1440) / 60);
                $remMins = $totalMinutes % 60;
                $remSecs = $totalSeconds % 60;

                $set('durasi_menit', $totalMinutes);
                $set('durasi_jam_menit', sprintf('%d:%02d', $hours, $mins));
                $set('durasi_lengkap', "{$days} hari {$remHours} jam {$remMins} menit {$remSecs} detik");
            } else {
                $set('durasi_menit', null);
                $set('durasi_jam_menit', null);
                $set('durasi_lengkap', 'Waktu selesai harus >= waktu mulai');
            }
        } catch (\Throwable) {
            // Abaikan parsing error saat user sedang mengetik
        }
    }
}

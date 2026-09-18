<?php

namespace App\Filament\Resources\Tikets\Schemas;

use App\Models\Tiket;
use Carbon\Carbon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class TiketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 2])
            ->components([
                // ==============================================================
                // 1. BANNER UTAMA: RINGKASAN TIKET & STATUS OPERASIONAL (FULL-WIDTH)
                // ==============================================================
                Section::make('Ringkasan Tiket & Status Operasional')
                    ->description('Identitas tiket dan status pemantauan SLA operasional secara seketika')
                    ->icon('heroicon-o-ticket')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 4])->schema([
                            TextEntry::make('nomor_tiket')
                                ->label('Nomor Tiket')
                                ->badge()
                                ->color('primary')
                                ->copyable()
                                ->fontFamily('mono')
                                ->size(TextSize::Medium),

                            TextEntry::make('status')
                                ->label('Status Operasional')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'Open' => 'warning',
                                    'Closed' => 'success',
                                    default => 'gray',
                                })
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'Open' => 'Open (Dalam Penanganan)',
                                    'Closed' => 'Closed (Selesai)',
                                    default => $state,
                                })
                                ->icon(fn (string $state): string => match ($state) {
                                    'Open' => 'heroicon-m-exclamation-circle',
                                    'Closed' => 'heroicon-m-check-circle',
                                    default => 'heroicon-m-question-mark-circle',
                                }),

                            TextEntry::make('kategori_problem')
                                ->label('Kategori Gangguan')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'Mesin ATM' => 'primary',
                                    'System' => 'info',
                                    'Jaringan' => 'warning',
                                    'Listrik' => 'danger',
                                    default => 'gray',
                                })
                                ->icon(fn (?string $state): string => match ($state) {
                                    'Mesin ATM' => 'heroicon-m-cpu-chip',
                                    'System' => 'heroicon-m-computer-desktop',
                                    'Jaringan' => 'heroicon-m-signal',
                                    'Listrik' => 'heroicon-m-bolt',
                                    default => 'heroicon-m-wrench',
                                }),

                            TextEntry::make('durasi_lengkap')
                                ->label('Total Durasi Downtime (SLA)')
                                ->state(function (Tiket $record): string {
                                    if ($record->status === 'Closed' && filled($record->durasi_lengkap)) {
                                        return $record->durasi_lengkap;
                                    }

                                    if ($record->mulai) {
                                        $mins = max(0, (int) Carbon::parse($record->mulai)->diffInMinutes(now()));
                                        $days = floor($mins / 1440);
                                        $hours = floor(($mins % 1440) / 60);
                                        $remMins = $mins % 60;
                                        if ($days > 0) {
                                            return "{$days} hari {$hours} jam {$remMins} mnt (Berjalan)";
                                        }

                                        return "{$hours} jam {$remMins} mnt (Berjalan)";
                                    }

                                    return 'Masih Berlangsung (Open)';
                                })
                                ->badge()
                                ->color(fn (Tiket $record): string => $record->status === 'Closed' ? 'success' : 'warning')
                                ->icon('heroicon-m-clock'),
                        ]),
                    ]),

                // ==============================================================
                // 2. KOLOM KIRI: RINCIAN KENDALA & KRONOLOGI PENANGANAN (SLA)
                // ==============================================================
                Section::make('Rincian Kendala & Kronologi Waktu (SLA)')
                    ->description('Kronologi terjadinya gangguan, waktu penanganan, dan detail catatan teknis')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('permasalahan')
                                ->label('Permasalahan Utama')
                                ->weight('bold')
                                ->size(TextSize::Large)
                                ->columnSpanFull(),

                            TextEntry::make('deskripsi')
                                ->label('Detail Catatan & Gejala Kerusakan')
                                ->placeholder('Tidak ada catatan tambahan.')
                                ->columnSpanFull(),

                            TextEntry::make('mulai')
                                ->label('Waktu Open (Mulai Gangguan)')
                                ->dateTime('d M Y, H:i:s')
                                ->icon('heroicon-m-calendar-days')
                                ->fontFamily('mono'),

                            TextEntry::make('selesai')
                                ->label('Waktu Closed (Selesai Perbaikan)')
                                ->dateTime('d M Y, H:i:s')
                                ->icon('heroicon-m-check-circle')
                                ->fontFamily('mono')
                                ->placeholder('Masih Open (Sedang Ditangani)'),

                            TextEntry::make('durasi_jam_menit')
                                ->label('Format Jam : Menit')
                                ->fontFamily('mono')
                                ->state(function (Tiket $record): string {
                                    if ($record->status === 'Closed' && filled($record->durasi_jam_menit)) {
                                        return $record->durasi_jam_menit;
                                    }

                                    if ($record->mulai) {
                                        $mins = max(0, (int) Carbon::parse($record->mulai)->diffInMinutes(now()));
                                        $hours = floor($mins / 60);
                                        $remMins = $mins % 60;

                                        return sprintf('~%d:%02d', $hours, $remMins);
                                    }

                                    return '-';
                                }),

                            TextEntry::make('durasi_menit')
                                ->label('Total Waktu Gangguan (Menit)')
                                ->fontFamily('mono')
                                ->state(function (Tiket $record): string {
                                    if ($record->status === 'Closed' && filled($record->durasi_menit)) {
                                        return number_format($record->durasi_menit, 0, ',', '.').' Menit';
                                    }

                                    if ($record->mulai) {
                                        $mins = max(0, (int) Carbon::parse($record->mulai)->diffInMinutes(now()));

                                        return '~'.number_format($mins, 0, ',', '.').' Menit';
                                    }

                                    return '-';
                                }),

                            TextEntry::make('tindakan')
                                ->label('Tindakan / Solusi Perbaikan')
                                ->placeholder('Belum ada catatan tindakan perbaikan.')
                                ->columnSpanFull()
                                ->visible(fn (Tiket $record) => filled($record->tindakan) || $record->status === 'Closed'),

                            TextEntry::make('created_at')
                                ->label('Waktu Tiket Dicatat di Sistem')
                                ->dateTime('d M Y, H:i:s')
                                ->fontFamily('mono')
                                ->color('gray')
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ==============================================================
                // 3. KOLOM KANAN: INFORMASI TERMINAL ATM / CRM TERKAIT
                // ==============================================================
                Section::make('Informasi Terminal ATM / CRM Terkait')
                    ->description('Detail spesifikasi, nomor identitas, dan lokasi perangkat terdampak')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('lokasi')
                                ->label('Nama Lokasi Fisik ATM')
                                ->state(fn (Tiket $record) => $record->lokasi ?? $record->terminal?->nama_lokasi ?? '-')
                                ->weight('bold')
                                ->size(TextSize::Large)
                                ->icon('heroicon-m-map-pin')
                                ->columnSpanFull(),

                            TextEntry::make('atm_id')
                                ->label('ID Mesin / LUNO')
                                ->state(fn (Tiket $record) => $record->atm_id ?? $record->terminal?->luno ?? '-')
                                ->fontFamily('mono')
                                ->badge()
                                ->color('primary')
                                ->copyable(),

                            TextEntry::make('profil')
                                ->label('Profile ATM')
                                ->state(fn (Tiket $record) => $record->profil ?? $record->terminal?->profil ?? '-')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('tipe_mesin')
                                ->label('Tipe / Merek Mesin')
                                ->state(fn (Tiket $record) => $record->tipe_mesin ?? $record->terminal?->tipe_mesin ?? '-')
                                ->badge()
                                ->color('gray'),

                            TextEntry::make('serial_number')
                                ->label('Serial Number (SN)')
                                ->state(fn (Tiket $record) => $record->serial_number ?? $record->terminal?->serial_number ?? '-')
                                ->fontFamily('mono')
                                ->copyable(),

                            TextEntry::make('cabang')
                                ->label('Kantor Cabang Pengelola')
                                ->state(fn (Tiket $record) => $record->cabang?->label_cabang ?? $record->cabang_text ?? $record->terminal?->cabang?->label_cabang ?? '-')
                                ->icon('heroicon-m-building-office-2')
                                ->columnSpanFull(),

                            TextEntry::make('terminal.vendor.nama_vendor')
                                ->label('Vendor Pemeliharaan')
                                ->badge()
                                ->icon('heroicon-m-wrench-screwdriver')
                                ->color(fn (?string $state): string => match ($state) {
                                    'SRISHINDU', 'SRISHINDU INFORMATIKA' => 'purple',
                                    'ASSINDO' => 'info',
                                    'KOPERASI BANK SULTENG' => 'success',
                                    'COLLEGA INTI PRATAMA' => 'warning',
                                    'PT KIS' => 'primary',
                                    default => 'gray',
                                })
                                ->wrap()
                                ->placeholder('-'),

                            TextEntry::make('terminal.kategori')
                                ->label('Kategori Mesin')
                                ->state(fn (Tiket $record) => $record->terminal?->kategori ?? '-')
                                ->badge()
                                ->color(fn ($state) => $state === 'CRM' ? 'success' : 'primary'),

                            TextEntry::make('terminal.ip_address')
                                ->label('IP Address')
                                ->fontFamily('mono')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('terminal.port')
                                ->label('Port Switch')
                                ->fontFamily('mono')
                                ->placeholder('-'),
                        ]),
                    ]),
            ]);
    }
}

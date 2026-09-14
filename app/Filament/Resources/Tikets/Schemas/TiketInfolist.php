<?php

namespace App\Filament\Resources\Tikets\Schemas;

use App\Models\Tiket;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TiketInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tiket & Status')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('nomor_tiket')
                                ->label('Nomor Tiket')
                                ->badge()
                                ->color('primary')
                                ->copyable(),

                            TextEntry::make('status')
                                ->label('Status Tiket')
                                ->badge()
                                ->color(fn (string $state): string => match ($state) {
                                    'Open' => 'warning',
                                    'Closed' => 'success',
                                    default => 'gray',
                                }),
                        ]),
                    ]),

                Section::make('Data Mesin ATM (Sesuai Kolom Excel)')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('lokasi')
                                ->label('Lokasi')
                                ->state(fn (Tiket $record) => $record->lokasi ?? $record->terminal?->nama_lokasi ?? '-'),

                            TextEntry::make('atm_id')
                                ->label('ID (ID Mesin / LUNO)')
                                ->state(fn (Tiket $record) => $record->atm_id ?? $record->terminal?->luno ?? '-')
                                ->fontFamily('mono'),

                            TextEntry::make('profil')
                                ->label('Profile ATM')
                                ->state(fn (Tiket $record) => $record->profil ?? $record->terminal?->profil ?? '-')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('tipe_mesin')
                                ->label('Type (Tipe Mesin)')
                                ->state(fn (Tiket $record) => $record->tipe_mesin ?? $record->terminal?->tipe_mesin ?? '-')
                                ->badge()
                                ->color('gray'),

                            TextEntry::make('serial_number')
                                ->label('SN (Serial Number)')
                                ->state(fn (Tiket $record) => $record->serial_number ?? $record->terminal?->serial_number ?? '-')
                                ->fontFamily('mono'),

                            TextEntry::make('cabang.label_cabang')
                                ->label('Cabang Pengelola')
                                ->placeholder(fn (Tiket $record) => $record->cabang_text ?? '-'),
                        ]),
                    ]),

                Section::make('Kendala & Waktu Gangguan (SLA)')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('permasalahan')
                                ->label('Permasalahan')
                                ->weight('bold'),

                            TextEntry::make('kategori_problem')
                                ->label('Kategori Problem')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'Mesin ATM' => 'primary',
                                    'System' => 'info',
                                    'Jaringan' => 'warning',
                                    'Listrik' => 'danger',
                                    default => 'gray',
                                }),

                            TextEntry::make('created_at')
                                ->label('Waktu Dicatat di Sistem')
                                ->dateTime('d M Y H:i:s'),

                            TextEntry::make('mulai')
                                ->label('Open Tiket (Start)')
                                ->dateTime('d/m/Y H:i:s'),

                            TextEntry::make('selesai')
                                ->label('Closed Tiket (End)')
                                ->dateTime('d/m/Y H:i:s')
                                ->placeholder('Masih Open (Berjalan)'),

                            TextEntry::make('durasi_lengkap')
                                ->label('1. Durasi Lengkap')
                                ->placeholder('Masih Berjalan (Open)'),

                            TextEntry::make('durasi_jam_menit')
                                ->label('2. Durasi Jam:Menit')
                                ->fontFamily('mono')
                                ->placeholder('-'),

                            TextEntry::make('durasi_menit')
                                ->label('3. Total Menit')
                                ->fontFamily('mono')
                                ->formatStateUsing(fn ($state) => $state ? "{$state} Menit" : '-'),

                            TextEntry::make('deskripsi')
                                ->label('Detail Catatan Kendala')
                                ->columnSpan(3)
                                ->placeholder('Tidak ada catatan tambahan.'),
                        ]),
                    ]),
            ]);
    }
}

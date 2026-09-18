<?php

namespace App\Filament\Resources\Terminals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class TerminalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Lokasi Terminal')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('profil')
                                ->label('Profil ATM')
                                ->badge()
                                ->color('primary')
                                ->copyable(),

                            TextEntry::make('nama_lokasi')
                                ->label('Nama Lokasi Fisik'),

                            TextEntry::make('cabang.label_cabang')
                                ->label('Kantor Cabang Pengelola'),

                            TextEntry::make('urutan_cabang')
                                ->label('Nomor Urut Per Cabang')
                                ->formatStateUsing(fn ($state) => $state ? "Unit ke-{$state}" : '-'),
                        ]),
                    ]),

                Section::make('Konfigurasi Jaringan & Switch')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('ip_address')
                                ->label('IP Address')
                                ->fontFamily('mono')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('luno')
                                ->label('ID / LUNO')
                                ->fontFamily('mono')
                                ->weight('bold')
                                ->badge()
                                ->size(TextSize::Medium)
                                ->color('gray')
                                ->copyable()
                                ->placeholder('-')
                                ->extraAttributes([
                                    'class' => 'fi-luno-badge font-mono font-bold text-sm tracking-wide',
                                ]),

                            TextEntry::make('port')
                                ->label('Port Switch')
                                ->fontFamily('mono')
                                ->placeholder('-'),
                        ]),
                    ]),

                Section::make('Spesifikasi Mesin & Vendor')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('kategori')
                                ->label('Kategori Alat')
                                ->badge()
                                ->color(fn (string $state): string => $state === 'CRM' ? 'success' : 'primary'),

                            TextEntry::make('tipe_mesin')
                                ->label('Tipe / Merek Mesin'),

                            TextEntry::make('serial_number')
                                ->label('Serial Number (SN)')
                                ->fontFamily('mono')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('denom')
                                ->label('Denominasi')
                                ->badge()
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    '100' => 'Rp 100.000',
                                    '50' => 'Rp 50.000',
                                    '50/100' => 'Rp 50.000 & 100.000 (Dual/CRM)',
                                    default => $state,
                                })
                                ->color(fn (string $state): string => match ($state) {
                                    '100' => 'danger',
                                    '50' => 'info',
                                    '50/100' => 'success',
                                    default => 'gray',
                                }),

                            TextEntry::make('vendor.nama_vendor')
                                ->label('Vendor Maintenance')
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
                                ->columnSpan(['default' => 1, 'sm' => 2])
                                ->wrap(),

                            TextEntry::make('is_hibah')
                                ->label('Status Hibah')
                                ->badge()
                                ->formatStateUsing(fn (bool $state): string => $state ? 'Mesin Hibah' : 'Aset Standar')
                                ->color(fn (bool $state): string => $state ? 'warning' : 'gray')
                                ->columnSpanFull(),

                            TextEntry::make('keterangan')
                                ->label('Catatan Keterangan')
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}

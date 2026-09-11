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
                        Grid::make(3)->schema([
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

                            TextEntry::make('created_at')
                                ->label('Waktu Dibuat di Sistem')
                                ->dateTime('d M Y H:i:s'),
                        ]),
                    ]),

                Section::make('Data Mesin ATM & Lokasi')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('terminal.profil')
                                ->label('Profil ATM')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('terminal.nama_lokasi')
                                ->label('Nama Lokasi Fisik'),

                            TextEntry::make('cabang.label_cabang')
                                ->label('Cabang / Capem / Kas')
                                ->placeholder(fn (Tiket $record) => $record->cabang_text ?? '-'),

                            TextEntry::make('terminal.luno')
                                ->label('ID Mesin / LUNO')
                                ->fontFamily('mono')
                                ->placeholder('-'),

                            TextEntry::make('terminal.serial_number')
                                ->label('Serial Number (SN)')
                                ->fontFamily('mono')
                                ->placeholder('-'),

                            TextEntry::make('terminal.tipe_mesin')
                                ->label('Tipe Mesin ATM')
                                ->badge()
                                ->color('gray')
                                ->placeholder('-'),

                            TextEntry::make('terminal.vendor.nama_vendor')
                                ->label('Vendor Pemeliharaan')
                                ->placeholder(fn (Tiket $record) => $record->terminal?->vendor_text ?? '-'),
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

                            TextEntry::make('durasi_lengkap')
                                ->label('Durasi Problem (Format Lengkap)')
                                ->placeholder('Masih Berjalan (Open)'),

                            TextEntry::make('mulai')
                                ->label('Waktu Open Tiket (Mulai)')
                                ->dateTime('d/m/Y H:i:s'),

                            TextEntry::make('selesai')
                                ->label('Waktu Closed Tiket (Selesai)')
                                ->dateTime('d/m/Y H:i:s')
                                ->placeholder('Tiket Belum Ditutup (Open)'),

                            TextEntry::make('durasi_menit')
                                ->label('Total Down Time (Menit)')
                                ->formatStateUsing(fn ($state, Tiket $record) => $state ? "{$state} menit ({$record->durasi_jam_menit} jam)" : '-'),

                            TextEntry::make('deskripsi')
                                ->label('Deskripsi Masalah')
                                ->columnSpan(3)
                                ->placeholder('Tidak ada catatan tambahan.'),
                        ]),
                    ]),

                Section::make('Kontak Pelapor & Tindakan Perbaikan')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('contact_person')
                                ->label('Contact Person (PIC / Pelapor)')
                                ->placeholder('-'),

                            TextEntry::make('phone_number')
                                ->label('Nomor Telepon / WhatsApp')
                                ->placeholder('-'),

                            TextEntry::make('tindakan')
                                ->label('Tindakan / Solusi Perbaikan')
                                ->columnSpan(2)
                                ->placeholder('Belum ada catatan tindakan perbaikan.'),
                        ]),
                    ]),
            ]);
    }
}

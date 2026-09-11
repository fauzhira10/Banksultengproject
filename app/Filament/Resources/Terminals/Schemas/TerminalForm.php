<?php

namespace App\Filament\Resources\Terminals\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TerminalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas & Lokasi Terminal')
                    ->description('Informasi profil, kode cabang, dan lokasi fisik ATM/CRM')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('profil')
                                ->label('Kode Profil ATM')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->placeholder('Contoh: WCR.KCU1, CRM.KCU1')
                                ->helperText('Format standar Bank Sulteng: WCR.*, DBL.*, NCR.*, CRM.*, YHU.*')
                                ->autocapitalize('characters'),

                            TextInput::make('nama_lokasi')
                                ->label('Nama Lokasi Fisik')
                                ->required()
                                ->placeholder('Contoh: RS Undata Palu, Kantor Samsat'),

                            Select::make('cabang_id')
                                ->label('Cabang Pengelola')
                                ->relationship('cabang', 'label_cabang')
                                ->searchable()
                                ->preload()
                                ->required(),

                            TextInput::make('urutan_cabang')
                                ->label('Nomor Urut Per Cabang')
                                ->numeric()
                                ->placeholder('1, 2, 3...'),

                            TextInput::make('rek_ia')
                                ->label('Nomor Rekening IA (Inter-Account)')
                                ->placeholder('Contoh: 101.01.00192')
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Konfigurasi Jaringan & Switch')
                    ->description('Alamat IP, ID/LUNO, dan port switch jaringan')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('ip_address')
                                ->label('IP Address')
                                ->placeholder('10.10.10.26')
                                ->ipv4(),

                            TextInput::make('luno')
                                ->label('ID / LUNO')
                                ->placeholder('0200')
                                ->maxLength(20),

                            TextInput::make('port')
                                ->label('Port Switch')
                                ->placeholder('8191')
                                ->maxLength(20),
                        ]),
                    ]),

                Section::make('Spesifikasi Mesin & Vendor')
                    ->description('Detail perangkat keras, vendor pemeliharaan, dan denominasi')
                    ->icon('heroicon-o-cpu-chip')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('kategori')
                                ->label('Kategori Mesin')
                                ->options([
                                    'ATM' => 'ATM (Tarik Tunai)',
                                    'CRM' => 'CRM (Setor Tarik Tunai)',
                                ])
                                ->default('ATM')
                                ->required(),

                            Select::make('tipe_mesin')
                                ->label('Tipe / Merek Mesin')
                                ->options([
                                    'Diebold 529' => 'Diebold 529',
                                    'Diebold 522' => 'Diebold 522 (Hibah)',
                                    'Wincor 280' => 'Wincor 280',
                                    'NCR SS22E' => 'NCR SS22E',
                                    'YIHUA CRM' => 'YIHUA CRM',
                                    'YIHUA ATM' => 'YIHUA ATM',
                                ])
                                ->searchable(),

                            TextInput::make('serial_number')
                                ->label('Serial Number (SN)')
                                ->placeholder('Contoh: 56HG701702'),

                            Select::make('denom')
                                ->label('Denominasi Pecahan')
                                ->options([
                                    '100' => 'Rp 100.000',
                                    '50' => 'Rp 50.000',
                                    '50/100' => 'Rp 50.000 & 100.000 (Dual/CRM)',
                                ])
                                ->default('100')
                                ->required(),

                            Select::make('vendor_id')
                                ->label('Vendor Maintenance')
                                ->relationship('vendor', 'nama_vendor')
                                ->searchable()
                                ->preload(),

                            Select::make('status')
                                ->label('Status Operasional')
                                ->options([
                                    'Aktif' => 'Operasional Aktif',
                                    'Belum Digunakan' => 'Belum Digunakan / Siap Relokasi',
                                    'Di Gudang' => 'Di Gudang Thamrin',
                                    'Maintenance' => 'Dalam Perbaikan (Offline)',
                                ])
                                ->default('Aktif')
                                ->required(),

                            Toggle::make('is_hibah')
                                ->label('Status Mesin Hibah')
                                ->helperText('Centang jika mesin berasal dari hibah (misal Diebold 522)')
                                ->columnSpanFull(),

                            Textarea::make('keterangan')
                                ->label('Catatan Tambahan / Keterangan')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}

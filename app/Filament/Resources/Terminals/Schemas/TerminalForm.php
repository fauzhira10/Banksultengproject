<?php

namespace App\Filament\Resources\Terminals\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class TerminalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                // ==================== KOLOM KIRI (UTAMA - 2 KOLOM DI DESKTOP) ====================
                Group::make([
                    // 1. Identitas & Lokasi Terminal
                    Section::make('Identitas & Penempatan Fisik')
                        ->description('Informasi profil mesin, kategori, nama lokasi, dan kantor cabang pengelola')
                        ->icon('heroicon-o-building-storefront')
                        ->schema([
                            Grid::make(['default' => 1, 'sm' => 2])->schema([
                                TextInput::make('profil')
                                    ->label('Kode Profil ATM / CRM')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Contoh: WCR.KCU1, CRM.KCU1')
                                    ->prefixIcon('heroicon-m-qr-code')
                                    ->extraInputAttributes(['class' => 'font-mono font-bold tracking-wider uppercase'])
                                    ->helperText('Format standar Bank Sulteng: WCR.*, DBL.*, NCR.*, CRM.*, YHU.*')
                                    ->autocapitalize('characters'),

                                ToggleButtons::make('kategori')
                                    ->label('Kategori Mesin')
                                    ->options([
                                        'ATM' => 'ATM (Tarik Tunai)',
                                        'CRM' => 'CRM (Setor Tarik)',
                                    ])
                                    ->colors([
                                        'ATM' => 'primary',
                                        'CRM' => 'info',
                                    ])
                                    ->icons([
                                        'ATM' => 'heroicon-m-banknotes',
                                        'CRM' => 'heroicon-m-arrows-right-left',
                                    ])
                                    ->default('ATM')
                                    ->required()
                                    ->inline(),

                                TextInput::make('nama_lokasi')
                                    ->label('Nama Lokasi Fisik')
                                    ->required()
                                    ->placeholder('Contoh: RS Undata Palu, Kantor Samsat, Alfamidi Moh. Yamin')
                                    ->prefixIcon('heroicon-m-map-pin')
                                    ->columnSpanFull(),

                                Select::make('cabang_id')
                                    ->label('Cabang Pengelola')
                                    ->relationship('cabang', 'label_cabang')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->prefixIcon('heroicon-m-building-office-2'),

                                TextInput::make('urutan_cabang')
                                    ->label('Nomor Urut di Cabang')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Contoh: 1, 2, 3...')
                                    ->prefixIcon('heroicon-m-numbered-list')
                                    ->helperText('Urutan unit mesin pada cabang pengelola'),
                            ]),
                        ]),

                    // 2. Spesifikasi Perangkat Keras & Vendor Pemeliharaan
                    Section::make('Spesifikasi Mesin & Pemeliharaan Vendor')
                        ->description('Perangkat keras, nomor seri pabrik, pecahan uang, dan mitra vendor SLA')
                        ->icon('heroicon-o-cpu-chip')
                        ->schema([
                            Grid::make(['default' => 1, 'sm' => 2])->schema([
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
                                    ->searchable()
                                    ->prefixIcon('heroicon-m-tag'),

                                TextInput::make('serial_number')
                                    ->label('Serial Number (SN)')
                                    ->placeholder('Contoh: 56HG701702, B2010D00482')
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->extraInputAttributes(['class' => 'font-mono uppercase']),

                                Select::make('denom')
                                    ->label('Denominasi Pecahan Uang')
                                    ->options([
                                        '100' => 'Rp 100.000 (Seratus Ribu)',
                                        '50' => 'Rp 50.000 (Lima Puluh Ribu)',
                                        '50/100' => 'Rp 50.000 & Rp 100.000 (Dual / CRM)',
                                    ])
                                    ->default('100')
                                    ->required()
                                    ->prefixIcon('heroicon-m-banknotes'),

                                Select::make('vendor_id')
                                    ->label('Vendor Pemeliharaan (SLA)')
                                    ->relationship('vendor', 'nama_vendor')
                                    ->searchable()
                                    ->preload()
                                    ->prefixIcon('heroicon-m-wrench-screwdriver')
                                    ->helperText('Mitra vendor penanggung jawab pemeliharaan SLA'),

                                Toggle::make('is_hibah')
                                    ->label('Status Mesin Hibah')
                                    ->helperText('Aktifkan jika unit mesin merupakan barang hibah (misal: Diebold 522)')
                                    ->columnSpanFull(),

                                Textarea::make('keterangan')
                                    ->label('Catatan Tambahan / Keterangan Penempatan')
                                    ->rows(3)
                                    ->placeholder('Catatan teknis khusus, kontak PIC lokasi, rute kunci brankas, dsb.')
                                    ->columnSpanFull(),
                            ]),
                        ]),
                ])->columnSpan(['default' => 1, 'lg' => 2]),

                // ==================== KOLOM KANAN (SIDEBAR / JARINGAN - 1 KOLOM) ====================
                Group::make([
                    Section::make('Konfigurasi Jaringan & Switch')
                        ->description('Parameter komunikasi switch dan monitoring operasional')
                        ->icon('heroicon-o-signal')
                        ->schema([
                            TextInput::make('ip_address')
                                ->label('IP Address')
                                ->placeholder('Contoh: 10.10.10.26')
                                ->prefixIcon('heroicon-m-globe-alt')
                                ->extraInputAttributes(['class' => 'font-mono font-semibold'])
                                ->helperText('Alamat IP privat terminal pada jaringan VPN/MPLS')
                                ->ipv4(),

                            TextInput::make('luno')
                                ->label('Terminal ID / LUNO')
                                ->placeholder('Contoh: 0200')
                                ->prefixIcon('heroicon-m-identification')
                                ->extraInputAttributes(['class' => 'font-mono font-semibold uppercase'])
                                ->helperText('Identitas logical unit terminal switch')
                                ->maxLength(20),

                            TextInput::make('port')
                                ->label('Port Switch')
                                ->placeholder('Contoh: 8191')
                                ->prefixIcon('heroicon-m-server')
                                ->extraInputAttributes(['class' => 'font-mono font-semibold'])
                                ->helperText('Port koneksi ke server switch Bank Sulteng')
                                ->maxLength(20),
                        ]),

                    Section::make('Panduan & Standar Bank Sulteng')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Placeholder::make('panduan_standar')
                                ->hiddenLabel()
                                ->content(new HtmlString('
                                    <div class="space-y-2.5 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                        <div class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                            <span class="inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                                            Standar Penamaan Profil:
                                        </div>
                                        <ul class="list-disc list-inside space-y-1 pl-1 text-[11.5px]">
                                            <li><code class="font-mono font-bold text-blue-700 dark:text-blue-300">WCR.*</code>: Wincor Nixdorf</li>
                                            <li><code class="font-mono font-bold text-blue-700 dark:text-blue-300">DBL.*</code>: Diebold</li>
                                            <li><code class="font-mono font-bold text-blue-700 dark:text-blue-300">NCR.*</code>: NCR Corporation</li>
                                            <li><code class="font-mono font-bold text-blue-700 dark:text-blue-300">CRM.*</code>: Cash Recycling Machine</li>
                                            <li><code class="font-mono font-bold text-blue-700 dark:text-blue-300">YHU.*</code>: Yihua ATM/CRM</li>
                                        </ul>
                                        <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400">
                                            Data terminal ini terhubung langsung dengan kalkulasi laporan SLA bulanan dan modul tiket insiden.
                                        </div>
                                    </div>
                                ')),
                        ]),
                ])->columnSpan(['default' => 1, 'lg' => 1]),
            ]);
    }
}

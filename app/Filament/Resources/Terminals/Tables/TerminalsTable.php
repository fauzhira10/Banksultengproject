<?php

namespace App\Filament\Resources\Terminals\Tables;

use App\Models\Terminal;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class TerminalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('profil')
                    ->label('Profil ATM')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable()
                    ->description(fn (Terminal $record): string => $record->kategori === 'CRM' ? 'CRM (Setor Tarik)' : ($record->is_hibah ? 'ATM Hibah' : 'ATM Tarik Tunai'))
                    ->badge()
                    ->color(fn (Terminal $record): string => $record->kategori === 'CRM' ? 'success' : ($record->is_hibah ? 'warning' : 'primary')),

                TextColumn::make('cabang.label_cabang')
                    ->label('Cabang')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Terminal $record): string => $record->urutan_cabang ? "Unit ke-{$record->urutan_cabang}" : ''),

                TextColumn::make('nama_lokasi')
                    ->label('Nama Lokasi Fisik')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('luno')
                    ->label('LUNO')
                    ->fontFamily('mono')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('port')
                    ->label('Port')
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('-'),

                TextColumn::make('tipe_mesin')
                    ->label('Tipe / Merek')
                    ->sortable()
                    ->description(fn (Terminal $record): string => $record->serial_number ? "SN: {$record->serial_number}" : ''),

                TextColumn::make('denom')
                    ->label('Pecahan')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        '100' => '100.000',
                        '50' => '50.000',
                        '50/100' => '50K & 100K',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        '100' => 'danger',
                        '50' => 'info',
                        '50/100' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('vendor.nama_vendor')
                    ->label('Vendor')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'SRISHINDU' => 'purple',
                        'ASSINDO' => 'info',
                        'HIBAH', 'HIBAH SRISHINDU' => 'warning',
                        default => 'gray',
                    }),

                TextInputColumn::make('rek_ia')
                    ->label('Rekening IA')
                    ->placeholder('Klik untuk isi...')
                    ->sortable()
                    ->searchable(),

                SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'Aktif' => 'Aktif',
                        'Belum Digunakan' => 'Belum Digunakan',
                        'Di Gudang' => 'Di Gudang',
                        'Maintenance' => 'Perbaikan',
                    ])
                    ->selectablePlaceholder(false),
            ])
            ->defaultSort('id', 'asc')
            ->filters([
                SelectFilter::make('cabang_id')
                    ->label('Filter Cabang')
                    ->relationship('cabang', 'label_cabang')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('vendor_id')
                    ->label('Filter Vendor')
                    ->relationship('vendor', 'nama_vendor')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('kategori')
                    ->label('Kategori Alat')
                    ->options([
                        'ATM' => 'ATM (Tarik Tunai)',
                        'CRM' => 'CRM (Setor Tarik Tunai)',
                    ]),

                SelectFilter::make('denom')
                    ->label('Pecahan Uang')
                    ->options([
                        '100' => 'Rp 100.000',
                        '50' => 'Rp 50.000',
                        '50/100' => '50K & 100K (CRM)',
                    ]),

                TernaryFilter::make('is_hibah')
                    ->label('Mesin Hibah'),

                SelectFilter::make('status')
                    ->label('Status Operasional')
                    ->options([
                        'Aktif' => 'Operasional Aktif',
                        'Belum Digunakan' => 'Belum Digunakan',
                        'Di Gudang' => 'Di Gudang Thamrin',
                        'Maintenance' => 'Dalam Perbaikan',
                    ]),

                TrashedFilter::make(),
            ])
            ->groups([
                Group::make('cabang.label_cabang')
                    ->label('Kantor Cabang')
                    ->collapsible(),
                Group::make('vendor.nama_vendor')
                    ->label('Vendor Maintenance')
                    ->collapsible(),
                Group::make('kategori')
                    ->label('Kategori Mesin')
                    ->collapsible(),
            ])
            ->recordActionsColumnLabel('Aksi')
            ->recordActionsAlignment('center')
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->color('info')
                    ->button()
                    ->size(Size::ExtraSmall)
                    ->slideOver(),

                EditAction::make()
                    ->label('Ubah')
                    ->icon('heroicon-m-pencil-square')
                    ->color('primary')
                    ->button()
                    ->size(Size::ExtraSmall)
                    ->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}

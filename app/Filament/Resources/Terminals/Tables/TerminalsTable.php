<?php

namespace App\Filament\Resources\Terminals\Tables;

use App\Models\Terminal;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TerminalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['cabang', 'vendor']))
            ->recordUrl(null)
            ->recordAction(null)
            ->searchPlaceholder('Cari Profil ATM, Lokasi, Cabang, IP, LUNO, SN, Vendor, Tipe...')
            ->searchDebounce('400ms')
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
                    ->searchable(query: fn ($query, string $search) => $query->where(fn ($q) => $q->where('tipe_mesin', 'like', "%{$search}%")->orWhere('serial_number', 'like', "%{$search}%")))
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
                    ->searchable()
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'SRISHINDU' => 'purple',
                        'ASSINDO' => 'info',
                        'HIBAH', 'HIBAH SRISHINDU' => 'warning',
                        default => 'gray',
                    }),
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

                TrashedFilter::make(),
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter Data')
                    ->icon('heroicon-m-funnel')
                    ->slideOver()
            )
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
                    ->label('Detail')
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

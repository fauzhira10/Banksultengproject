<?php

namespace App\Filament\Resources\Vendors\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Size;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari Nama Vendor, Kontak / PIC, Keterangan...')
            ->searchDebounce('400ms')
            ->columns([
                TextColumn::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('terminals_count')
                    ->label('Total Mesin')
                    ->counts('terminals')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('kontak')
                    ->label('Kontak / PIC')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap(),
            ])
            ->filters([
                TernaryFilter::make('has_terminals')
                    ->label('Pengelolaan Unit ATM/CRM')
                    ->placeholder('Semua Vendor')
                    ->trueLabel('Mengelola Unit ATM/CRM')
                    ->falseLabel('Belum Ada Unit Dikelola')
                    ->queries(
                        true: fn (Builder $query) => $query->has('terminals'),
                        false: fn (Builder $query) => $query->doesntHave('terminals'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->filtersTriggerAction(
                fn (Action $action) => $action
                    ->button()
                    ->label('Filter Data')
                    ->icon('heroicon-m-funnel')
            )
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
                    ->size(Size::ExtraSmall),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

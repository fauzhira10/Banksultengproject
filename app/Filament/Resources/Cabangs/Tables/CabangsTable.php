<?php

namespace App\Filament\Resources\Cabangs\Tables;

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

class CabangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari Kode atau Nama Cabang...')
            ->searchDebounce('400ms')
            ->columns([
                TextColumn::make('kode_cabang')
                    ->label('Kode Cabang')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_cabang')
                    ->label('Nama Cabang')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('terminals_count')
                    ->label('Jumlah ATM/CRM')
                    ->counts('terminals')
                    ->badge()
                    ->icon('heroicon-m-computer-desktop')
                    ->color(fn (?int $state): string => ($state ?? 0) > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn (?int $state): string => ($state ?? 0).' Unit')
                    ->tooltip(fn (?int $state): string => ($state ?? 0) > 0 ? "{$state} Unit ATM/CRM aktif di cabang ini" : 'Belum ada unit ATM/CRM terdaftar')
                    ->alignCenter()
                    ->sortable()
                    ->extraAttributes([
                        'class' => 'bs-terminals-count-col',
                    ]),
            ])
            ->defaultSort('kode_cabang', 'asc')
            ->filters([
                TernaryFilter::make('has_terminals')
                    ->label('Kepemilikan Unit ATM/CRM')
                    ->placeholder('Semua Cabang')
                    ->trueLabel('Ada Unit ATM/CRM')
                    ->falseLabel('Belum Ada Unit')
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

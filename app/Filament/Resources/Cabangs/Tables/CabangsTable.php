<?php

namespace App\Filament\Resources\Cabangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CabangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
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

                TextColumn::make('label_cabang')
                    ->label('Label Standar')
                    ->searchable(),

                TextColumn::make('terminals_count')
                    ->label('Jumlah ATM/CRM')
                    ->counts('terminals')
                    ->badge()
                    ->color('success')
                    ->sortable(),
            ])
            ->defaultSort('kode_cabang', 'asc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Cabangs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CabangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_cabang')
                    ->label('Kode Cabang')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('001, 002, 101...'),

                TextInput::make('nama_cabang')
                    ->label('Nama Cabang')
                    ->required()
                    ->placeholder('Utama Palu, Toli-Toli...'),

                TextInput::make('label_cabang')
                    ->label('Label Gabungan')
                    ->placeholder('001-Utama Palu'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Vendors\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class VendorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('kontak')
                    ->label('Kontak / PIC'),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3),
            ]);
    }
}

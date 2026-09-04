<?php

namespace App\Filament\Resources\Terminals;

use App\Filament\Resources\Terminals\Pages\CreateTerminal;
use App\Filament\Resources\Terminals\Pages\EditTerminal;
use App\Filament\Resources\Terminals\Pages\ListTerminals;
use App\Filament\Resources\Terminals\Pages\ViewTerminal;
use App\Filament\Resources\Terminals\Schemas\TerminalForm;
use App\Filament\Resources\Terminals\Schemas\TerminalInfolist;
use App\Filament\Resources\Terminals\Tables\TerminalsTable;
use App\Models\Terminal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TerminalResource extends Resource
{
    protected static ?string $model = Terminal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TerminalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TerminalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TerminalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTerminals::route('/'),
            'create' => CreateTerminal::route('/create'),
            'view' => ViewTerminal::route('/{record}'),
            'edit' => EditTerminal::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}

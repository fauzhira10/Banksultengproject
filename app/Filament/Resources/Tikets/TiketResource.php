<?php

namespace App\Filament\Resources\Tikets;

use App\Filament\Resources\Tikets\Pages\CreateTiket;
use App\Filament\Resources\Tikets\Pages\EditTiket;
use App\Filament\Resources\Tikets\Pages\ListTikets;
use App\Filament\Resources\Tikets\Pages\ViewTiket;
use App\Filament\Resources\Tikets\Schemas\TiketForm;
use App\Filament\Resources\Tikets\Schemas\TiketInfolist;
use App\Filament\Resources\Tikets\Tables\TiketsTable;
use App\Models\Tiket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TiketResource extends Resource
{
    protected static ?string $model = Tiket::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Open / Closed Tiket ATM';

    protected static ?string $modelLabel = 'Tiket ATM';

    protected static ?string $pluralModelLabel = 'Open / Closed Tiket ATM';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $openCount = Tiket::getCountsSummary()['open'];

        return $openCount > 0 ? (string) $openCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Tiket ATM yang sedang aktif (Open / Dalam Proses)';
    }

    public static function form(Schema $schema): Schema
    {
        return TiketForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TiketInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TiketsTable::configure($table);
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
            'index' => ListTikets::route('/'),
            'create' => CreateTiket::route('/create'),
            'view' => ViewTiket::route('/{record}'),
            'edit' => EditTiket::route('/{record}/edit'),
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

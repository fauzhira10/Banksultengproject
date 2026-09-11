<?php

namespace App\Filament\Resources\Tikets\Pages;

use App\Filament\Resources\Tikets\TiketResource;
use App\Models\Tiket;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTikets extends ListRecords
{
    protected static string $resource = TiketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Input Tiket Baru')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Tiket')
                ->badge(Tiket::count())
                ->badgeColor('primary'),

            'open' => Tab::make('Open (Dalam Proses)')
                ->badge(Tiket::where('status', 'Open')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Open')),

            'closed' => Tab::make('Closed (Selesai)')
                ->badge(Tiket::where('status', 'Closed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Closed')),

            'mesin' => Tab::make('Problem Mesin ATM')
                ->badge(Tiket::where('kategori_problem', 'Mesin ATM')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori_problem', 'Mesin ATM')),

            'jaringan_listrik' => Tab::make('Jaringan / Listrik / System')
                ->badge(Tiket::whereIn('kategori_problem', ['Jaringan', 'Listrik', 'System'])->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('kategori_problem', ['Jaringan', 'Listrik', 'System'])),
        ];
    }
}

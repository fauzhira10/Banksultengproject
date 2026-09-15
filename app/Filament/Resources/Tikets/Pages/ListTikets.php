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
        $counts = Tiket::getCountsSummary();

        return [
            'all' => Tab::make('Semua Tiket')
                ->badge($counts['total'])
                ->badgeColor('primary'),

            'open' => Tab::make('Open (Dalam Proses)')
                ->badge($counts['open'])
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Open')),

            'closed' => Tab::make('Closed (Selesai)')
                ->badge($counts['closed'])
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Closed')),

            'mesin' => Tab::make('Problem Mesin ATM')
                ->badge($counts['mesin'])
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori_problem', 'Mesin ATM')),

            'jaringan_listrik' => Tab::make('Jaringan / Listrik / System')
                ->badge($counts['jaringan_listrik'])
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('kategori_problem', ['Jaringan', 'Listrik', 'System'])),
        ];
    }
}

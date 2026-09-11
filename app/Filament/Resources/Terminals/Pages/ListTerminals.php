<?php

namespace App\Filament\Resources\Terminals\Pages;

use App\Filament\Resources\Terminals\TerminalResource;
use App\Filament\Resources\Terminals\Widgets\TerminalOverviewWidget;
use App\Models\Terminal;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTerminals extends ListRecords
{
    protected static string $resource = TerminalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Terminal Baru')
                ->icon('heroicon-m-plus')
                ->slideOver(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TerminalOverviewWidget::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua')
                ->badge(Terminal::count())
                ->badgeColor('primary'),

            'crm' => Tab::make('CRM (Setor Tarik)')
                ->badge(Terminal::crm()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'CRM')),

            'atm' => Tab::make('ATM Tarik Tunai')
                ->badge(Terminal::atm()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('kategori', 'ATM')),

            'hibah' => Tab::make('Mesin Hibah (D522)')
                ->badge(Terminal::hibah()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_hibah', true)),

            'gudang' => Tab::make('Belum Digunakan / Gudang')
                ->badge(Terminal::whereIn('status', ['Belum Digunakan', 'Di Gudang'])->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['Belum Digunakan', 'Di Gudang'])),
        ];
    }
}

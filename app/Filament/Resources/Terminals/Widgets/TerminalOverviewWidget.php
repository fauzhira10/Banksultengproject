<?php

namespace App\Filament\Resources\Terminals\Widgets;

use App\Models\Terminal;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TerminalOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $counts = Terminal::getCountsSummary();
        $total = $counts['total'];
        $crm = $counts['crm'];
        $atm = $counts['atm'];
        $hibah = $counts['hibah'];

        return [
            Stat::make('Total Terminal', "{$total} Unit")
                ->description('Seluruh Jaringan ATM & CRM Bank Sulteng')
                ->descriptionIcon('heroicon-m-server-stack')
                ->color('primary'),

            Stat::make('CRM (Setor Tarik)', "{$crm} Unit")
                ->description('YIHUA CRM Pecahan 50K & 100K')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('success'),

            Stat::make('ATM Tarik Tunai', "{$atm} Unit")
                ->description('Diebold, Wincor, NCR, Yihua ATM')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Mesin Hibah (D522)', "{$hibah} Unit")
                ->description('Diebold 522 & Unit Khusus')
                ->descriptionIcon('heroicon-m-gift')
                ->color('warning'),
        ];
    }
}

<?php

namespace App\Filament\Resources\Terminals\Pages;

use App\Filament\Resources\Terminals\TerminalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTerminal extends ViewRecord
{
    protected static string $resource = TerminalResource::class;

    protected static ?string $title = 'Rincian Terminal ATM & CRM';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * Breadcrumb diganti tombol "Kembali" (render hook `filament.hooks.terminal-view-back-button`).
     *
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [];
    }
}

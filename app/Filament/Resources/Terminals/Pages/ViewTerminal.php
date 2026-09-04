<?php

namespace App\Filament\Resources\Terminals\Pages;

use App\Filament\Resources\Terminals\TerminalResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTerminal extends ViewRecord
{
    protected static string $resource = TerminalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\Tikets\Pages;

use App\Filament\Resources\Tikets\TiketResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTiket extends ViewRecord
{
    protected static string $resource = TiketResource::class;

    protected static ?string $title = 'Rincian Tiket Masalah ATM';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    /**
     * Breadcrumb diganti tombol "Kembali" (render hook `filament.hooks.tiket-view-back-button`).
     *
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        return [];
    }
}

<?php

namespace App\Filament\Resources\Tikets\Pages;

use App\Filament\Resources\Tikets\TiketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTiket extends CreateRecord
{
    protected static string $resource = TiketResource::class;

    protected static ?string $title = 'Input Tiket Masalah ATM Baru';

    public function getSubheading(): ?string
    {
        return 'Daftarkan kendala atau kerusakan mesin ATM / CRM untuk pemantauan SLA operasional Bank Sulteng.';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

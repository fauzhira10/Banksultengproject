<?php

namespace App\Filament\Resources\Tikets\Pages;

use App\Filament\Resources\Tikets\TiketResource;
use App\Models\Tiket;
use Filament\Resources\Pages\CreateRecord;

class CreateTiket extends CreateRecord
{
    protected static string $resource = TiketResource::class;

    protected static bool $canCreateAnother = false;

    protected static ?string $title = 'Input Tiket Masalah ATM Baru';

    public function canCreateAnother(): bool
    {
        return false;
    }

    public function getSubheading(): ?string
    {
        return 'Daftarkan kendala atau kerusakan mesin ATM / CRM untuk pemantauan SLA operasional Bank Sulteng.';
    }

    protected function beforeValidate(): void
    {
        $currentNo = trim((string) ($this->data['nomor_tiket'] ?? ''));
        if ($currentNo === '') {
            $this->data['nomor_tiket'] = Tiket::generateNomorTiket();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $currentNo = trim((string) ($data['nomor_tiket'] ?? ''));
        if ($currentNo === '') {
            $data['nomor_tiket'] = Tiket::generateNomorTiket();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

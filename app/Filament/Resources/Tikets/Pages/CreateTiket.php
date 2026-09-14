<?php

namespace App\Filament\Resources\Tikets\Pages;

use App\Filament\Resources\Tikets\TiketResource;
use App\Models\Tiket;
use Filament\Resources\Pages\CreateRecord;

class CreateTiket extends CreateRecord
{
    protected static string $resource = TiketResource::class;

    protected static ?string $title = 'Input Tiket Masalah ATM Baru';

    public function getSubheading(): ?string
    {
        return 'Daftarkan kendala atau kerusakan mesin ATM / CRM untuk pemantauan SLA operasional Bank Sulteng.';
    }

    protected function beforeValidate(): void
    {
        $currentNo = $this->data['nomor_tiket'] ?? null;
        if (! $currentNo || Tiket::withTrashed()->where('nomor_tiket', $currentNo)->exists()) {
            $this->data['nomor_tiket'] = Tiket::generateNomorTiket();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['nomor_tiket']) || Tiket::withTrashed()->where('nomor_tiket', $data['nomor_tiket'])->exists()) {
            $data['nomor_tiket'] = Tiket::generateNomorTiket();
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

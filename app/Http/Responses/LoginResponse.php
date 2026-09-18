<?php

namespace App\Http\Responses;

use App\Filament\Resources\Tikets\TiketResource;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    /**
     * Reset riwayat pilihan tab, filter tabel, dan vendor saat login baru.
     */
    public static function resetSessionHistory(): void
    {
        session()->forget([
            'sla_selected_vendor_id',
            'tikets_active_tab',
            'terminals_active_tab',
            'tables',
        ]);

        foreach (array_keys(session()->all()) as $key) {
            if (str_starts_with($key, 'tables.')) {
                session()->forget($key);
            }
        }
    }

    public function toResponse($request): RedirectResponse|Redirector
    {
        static::resetSessionHistory();

        $intended = session()->pull('url.intended');
        $tiketsUrl = TiketResource::getUrl('index');

        // Langsung arahkan ke halaman utama Tiket jika intended kosong atau mengarah ke root /admin
        // guna mencegah terjadinya double redirect (302) antar rute.
        if (! $intended || rtrim($intended, '/') === rtrim(url('/admin'), '/') || rtrim($intended, '/') === rtrim(url('/'), '/')) {
            return redirect()->to($tiketsUrl);
        }

        return redirect()->to($intended);
    }
}

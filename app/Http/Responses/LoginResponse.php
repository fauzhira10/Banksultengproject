<?php

namespace App\Http\Responses;

use App\Filament\Resources\Tikets\TiketResource;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        session()->forget('sla_selected_vendor_id');

        return redirect()->intended(TiketResource::getUrl('index'));
    }
}

<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Sistem Monitoring SLA ATM';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            '<span class="block font-semibold text-slate-800 dark:text-slate-200">PT Bank Pembangunan Daerah Sulawesi Tengah</span>'.
            '<span class="mt-1 block text-xs text-slate-500 dark:text-slate-400">Portal Pemantauan Ketersediaan & Resolusi Kendala Jaringan ATM/CRM</span>'
        );
    }

    /**
     * Logo Bank Sulteng dirender lewat render hook `filament.hooks.login-logo`,
     * sehingga logo teks bawaan panel tidak ditampilkan di halaman login.
     */
    public function hasLogo(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
            ]);
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label('Username')
            ->required()
            ->autocomplete()
            ->autofocus();
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(#[SensitiveParameter] array $data): array
    {
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}

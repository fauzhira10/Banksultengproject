<?php

namespace App\Filament\Pages\Auth;

use App\Services\LoginCaptchaService;
use Closure;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Image;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use SensitiveParameter;

class Login extends BaseLogin
{
    /**
     * Gambar captcha aktif (data URI PNG). Kodenya hanya tersimpan di session sebagai hash.
     */
    #[Locked]
    public string $captchaImage = '';

    public function mount(): void
    {
        parent::mount();

        $this->refreshCaptcha();
    }

    /**
     * Captcha diganti setiap kali percobaan login selesai (berhasil maupun gagal),
     * sehingga satu kode tidak dapat dipakai untuk menebak password berulang kali.
     */
    public function authenticate(): ?LoginResponse
    {
        try {
            return parent::authenticate();
        } finally {
            $this->refreshCaptcha();
        }
    }

    public function refreshCaptcha(): void
    {
        $this->captchaImage = app(LoginCaptchaService::class)->generate();
        $this->data['captcha'] = null;
    }

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
                $this->getCaptchaImageComponent(),
                $this->getCaptchaFormComponent(),
            ]);
    }

    protected function getCaptchaImageComponent(): Component
    {
        return Image::make(fn (): string => $this->captchaImage, 'Kode captcha')
            ->imageWidth(180)
            ->imageHeight(56);
    }

    protected function getCaptchaFormComponent(): Component
    {
        return TextInput::make('captcha')
            ->label('Kode Captcha')
            ->placeholder('Ketik 5 karakter pada gambar')
            ->helperText('Tidak membedakan huruf besar/kecil.')
            ->required()
            ->maxLength(LoginCaptchaService::CODE_LENGTH)
            ->autocomplete(false)
            ->dehydrated(false)
            ->suffixAction(
                Action::make('refreshCaptcha')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->tooltip('Ganti gambar captcha')
                    ->action(fn () => $this->refreshCaptcha()),
            )
            ->rule(fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                if (! app(LoginCaptchaService::class)->verify(is_string($value) ? $value : null)) {
                    $fail('Kode captcha tidak sesuai atau sudah kedaluwarsa. Silakan coba lagi.');
                }
            });
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

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::auth/pages/login.form.actions.authenticate.label'))
            ->submit('authenticate')
            ->extraAttributes([
                'id' => 'bs-login-submit-btn',
            ]);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.username' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}

<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Models\AuditLog;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            LoginResponseContract::class,
            LoginResponse::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->centerDeleteConfirmationModals();
        $this->configureSecurityDefaults();
        $this->recordAuthenticationEvents();

        Event::listen(
            Login::class,
            fn () => LoginResponse::resetSessionHistory()
        );
    }

    /**
     * Kebijakan kata sandi kuat dan HTTPS wajib di lingkungan produksi.
     */
    protected function configureSecurityDefaults(): void
    {
        Password::defaults(fn (): Password => Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Mencatat login, logout, dan percobaan login gagal ke jejak audit.
     */
    protected function recordAuthenticationEvents(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            AuditLog::record('login', $event->user, userId: $event->user->getAuthIdentifier());
        });

        Event::listen(Logout::class, function (Logout $event): void {
            AuditLog::record('logout', $event->user, userId: $event->user?->getAuthIdentifier());
        });

        Event::listen(Failed::class, function (Failed $event): void {
            AuditLog::record(
                'login_failed',
                $event->user,
                newValues: ['username' => $event->credentials['username'] ?? null],
                userId: $event->user?->getAuthIdentifier(),
            );
        });
    }

    /**
     * Modal konfirmasi hapus ditampilkan tepat di tengah layar (lihat `.bs-modal-centered` di app.css).
     */
    protected function centerDeleteConfirmationModals(): void
    {
        $centerModal = fn (Action $action): Action => $action
            ->extraModalWindowAttributes(['class' => 'bs-modal-centered'], merge: true);

        DeleteAction::configureUsing($centerModal);
        DeleteBulkAction::configureUsing($centerModal);
        ForceDeleteAction::configureUsing($centerModal);
        ForceDeleteBulkAction::configureUsing($centerModal);
    }
}

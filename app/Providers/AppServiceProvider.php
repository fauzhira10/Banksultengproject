<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

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

        Event::listen(
            Login::class,
            fn () => session()->forget('sla_selected_vendor_id')
        );
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

<?php

namespace App\Providers;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->centerDeleteConfirmationModals();
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

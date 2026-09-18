<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\Terminals\Pages\ListTerminals;
use App\Filament\Resources\Terminals\Pages\ViewTerminal;
use App\Filament\Resources\Tikets\Pages\ListTikets;
use App\Filament\Resources\Tikets\Pages\ViewTiket;
use Filament\Enums\ThemeMode;
use Filament\Enums\UserMenuPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->homeUrl('/admin/tikets')
            ->login(Login::class)
            ->profile(EditProfile::class, isSimple: false)
            ->strictAuthorization()
            ->userMenu(position: UserMenuPosition::Sidebar)
            ->icons([
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => Heroicon::OutlinedBars3,
                PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => Heroicon::OutlinedBars3,
                PanelsIconAlias::TOPBAR_OPEN_SIDEBAR_BUTTON => Heroicon::OutlinedBars3,
                PanelsIconAlias::TOPBAR_CLOSE_SIDEBAR_BUTTON => Heroicon::OutlinedBars3,
            ])
            ->brandName('Monitoring SLA ATM - Bank Sulteng')
            ->font('Plus Jakarta Sans')
            ->monoFont('JetBrains Mono')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16.5rem')
            ->maxContentWidth(Width::Full)
            ->defaultThemeMode(ThemeMode::Light)
            ->darkMode(false)
            ->colors([
                'primary' => Color::Blue,
                'purple' => Color::Purple,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn () => view('filament.hooks.head-theme'),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn () => view('filament.hooks.vite-styles'),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn () => view('filament.hooks.table-scroll-to-end'),
                scopes: [
                    ListTikets::class,
                    ListTerminals::class,
                ],
            )
            ->renderHook(
                PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE,
                fn () => view('filament.hooks.tiket-view-back-button'),
                scopes: ViewTiket::class,
            )
            ->renderHook(
                PanelsRenderHook::PAGE_HEADER_HEADING_BEFORE,
                fn () => view('filament.hooks.terminal-view-back-button'),
                scopes: ViewTerminal::class,
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn () => view('filament.hooks.login-header'),
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_START,
                fn () => view('filament.hooks.login-logo'),
                scopes: Login::class,
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_PAGE_END,
                fn () => view('filament.hooks.login-loader'),
                scopes: Login::class,
            )
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_END,
                fn () => view('filament.hooks.login-footer'),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

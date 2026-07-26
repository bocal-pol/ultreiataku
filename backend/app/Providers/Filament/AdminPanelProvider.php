<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectToCentralAuth;
use App\Modules\Pilgrimage\Filament\Resources\TripResource;
use App\Modules\Pilgrimage\Filament\Resources\PilgrimResource;
use App\Modules\Pilgrimage\Filament\Widgets\AccommodationsToVerifyWidget;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
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
            // ADR-U06 — Pas de formulaire de login natif Filament
            // L'auth passe via RedirectToCentralAuth (SSO SiteV26)
            ->login(null)
            ->colors([
                // Palette OikoTaku — Terracotta (theme Ultreiataku)
                'primary' => Color::hex('#D96B43'),
            ])
            // Resources module Pilgrimage (vagues 1a/1b/1c)
            ->discoverResources(
                in: app_path('Modules/Pilgrimage/Filament/Resources'),
                for: 'App\Modules\Pilgrimage\Filament\Resources',
            )
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Modules/Pilgrimage/Filament/Widgets'),
                for: 'App\Modules\Pilgrimage\Filament\Widgets',
            )
            ->widgets([
                AccountWidget::class,
                AccommodationsToVerifyWidget::class,
            ])
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
            // ADR-U06 : SSO middleware — remplace Filament\Authenticate natif
            ->authMiddleware([
                RedirectToCentralAuth::class,
            ]);
    }
}

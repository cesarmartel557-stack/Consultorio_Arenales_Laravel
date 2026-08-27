<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->path('gestion')
            ->login(Login::class)
            ->defaultThemeMode(ThemeMode::Light)
            ->colors([
                'primary' => Color::hex('#0d3f52'),
            ])
            ->brandName('Consultorio Arenales')
            ->brandLogo(fn () => view('filament.logo'))
            ->brandLogoHeight('2.5rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                    /* Fondo turquesa para Navbar y Header lateral */
                    .fi-topbar > nav, .fi-sidebar-header { background-color: #0d3f52 !important; border-bottom: none !important; box-shadow: none !important; }
                    .fi-sidebar-header > div:first-child { width: 100% !important; }
                    .fi-sidebar-header a { width: 100% !important; display: block !important; }
                    /* Textos e íconos en topbar de color blanco, excluyendo paneles desplegables */
                    .fi-topbar button:not(.fi-dropdown-panel button), 
                    .fi-topbar .fi-icon-btn:not(.fi-dropdown-panel .fi-icon-btn) { color: white !important; }
                    
                    /* Fondo general de toda la pagina (blanco claro, negro oscuro) */
                    body, .fi-body, .fi-layout, .fi-simple-layout { background-color: #ffffff !important; }
                    .dark body, .dark .fi-body, .dark .fi-layout, .dark .fi-simple-layout { background-color: #000000 !important; }
                    
                    /* Caja de login turquesa */
                    .fi-simple-main { background-color: #0d3f52 !important; border: none !important; box-shadow: 0 10px 25px rgba(0,0,0,0.5) !important; }
                    .fi-simple-main .fi-header-heading, 
                    .fi-simple-main label, 
                    .fi-simple-main label span, 
                    .fi-simple-main p,
                    .fi-simple-main .text-gray-950,
                    .fi-simple-main a { color: white !important; }
                    
                    /* Inputs transparentes con bordes tenues (en modo claro y oscuro) */
                    .fi-simple-main .fi-input-wrp,
                    .fi-simple-main .fi-input-wrapper { background-color: rgba(0,0,0,0.25) !important; border: 1px solid rgba(255,255,255,0.25) !important; box-shadow: none !important; border-radius: 0.5rem !important; --tw-ring-color: transparent !important; }
                    .fi-simple-main .fi-input-wrp:focus-within,
                    .fi-simple-main .fi-input-wrapper:focus-within { border-color: rgba(255,255,255,0.7) !important; box-shadow: 0 0 0 1px rgba(255,255,255,0.7) !important; }
                    .fi-simple-main .fi-input { color: white !important; background-color: transparent !important; }
                    .fi-simple-main .fi-input::placeholder { color: rgba(255,255,255,0.5) !important; }
                    .fi-simple-main .fi-input-wrp .fi-icon-btn,
                    .fi-simple-main .fi-input-wrapper .fi-icon-btn { color: rgba(255,255,255,0.7) !important; }
                    .fi-simple-main .fi-checkbox-input { background-color: rgba(0,0,0,0.25) !important; border: 1px solid rgba(255,255,255,0.3) !important; }
                    
                    /* Evitar que el autocompletado ponga el fondo blanco */
                    .fi-simple-main .fi-input:-webkit-autofill,
                    .fi-simple-main .fi-input:-webkit-autofill:hover,
                    .fi-simple-main .fi-input:-webkit-autofill:focus,
                    .fi-simple-main .fi-input:-webkit-autofill:active {
                        -webkit-box-shadow: 0 0 0 30px #0a3342 inset !important;
                        -webkit-text-fill-color: white !important;
                        transition: background-color 5000s ease-in-out 0s;
                    }
                    
                    /* Botón de acceso con fondo oscuro */
                    .fi-simple-main .fi-btn { background-color: rgba(0,0,0,0.4) !important; border: 1px solid rgba(255,255,255,0.15) !important; color: white !important; }
                    .fi-simple-main .fi-btn:hover { background-color: rgba(0,0,0,0.6) !important; }

                    /* Diferenciar tamaño del logo en Navbar vs Login */
                    .custom-brand-logo { max-height: 2.5rem !important; }
                    .fi-simple-layout .fi-logo { height: 6rem !important; margin-bottom: 1rem !important; }
                    .fi-simple-layout .custom-brand-logo { max-height: 5.5rem !important; }
                </style>'
            );
    }
}

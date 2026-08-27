<?php

namespace App\Filament\Pages\Auth;

use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string|Htmlable
    {
        return 'Ingresar al panel';
    }

    public function authenticate(): ?LoginResponse
    {
        // Limpiar URL previa de la sesión para evitar redirigir a médicos a secciones de admin
        session()->forget('url.intended');

        return parent::authenticate();
    }
}

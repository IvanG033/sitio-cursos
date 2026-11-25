<?php

namespace App\Providers;

use App\Mail\AvisoMail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('partials.pagination');

        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Correo de "recuperar contraseña" en español y con el diseño del sitio.
        ResetPassword::toMailUsing(function ($user, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

            return (new AvisoMail(
                'Crea una nueva contraseña',
                "Hola {$user->name}, recibimos una solicitud para cambiar tu contraseña. El enlace vence en 60 minutos. Si no fuiste tú, ignora este correo.",
                'Crear nueva contraseña',
                $url,
            ))->to($user->email);
        });
    }
}

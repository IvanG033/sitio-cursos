<?php

namespace App\Support;

use App\Mail\AvisoMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envía correos sin romper la página si el servicio de correo falla
 * (por ejemplo, si todavía no se configuró la contraseña SMTP).
 */
class Correo
{
    public static function enviar(?string $para, string $asunto, string $cuerpo, ?string $botonTexto = null, ?string $botonUrl = null): bool
    {
        if (! $para) {
            return false;
        }

        try {
            Mail::to($para)->send(new AvisoMail($asunto, $cuerpo, $botonTexto, $botonUrl));

            return true;
        } catch (Throwable $e) {
            Log::warning('No se pudo enviar el correo a '.$para.': '.$e->getMessage());

            return false;
        }
    }
}

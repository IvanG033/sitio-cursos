<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $asunto }}</title></head>
<body style="margin:0;padding:0;background:#faf8f4;font-family:Arial,Helvetica,sans-serif;color:#3a3a3a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf8f4;padding:32px 12px;">
    <tr><td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border:1px solid #e6e1d8;border-radius:14px;">
            <tr><td style="padding:0;height:6px;background:#F2B705;border-radius:14px 14px 0 0;font-size:0;line-height:0;">&nbsp;</td></tr>
            <tr><td style="padding:24px 32px 8px;">
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('cursos.nombre') }}" width="99" height="80" style="display:block;border:0;">
            </td></tr>
            <tr><td style="padding:8px 32px 0;">
                <h1 style="font-size:22px;line-height:1.3;color:#1D2F42;margin:12px 0 16px;">{{ $asunto }}</h1>
                <div style="font-size:15px;line-height:1.6;">{!! nl2br(e($cuerpo)) !!}</div>
            </td></tr>
            @if($botonUrl)
            <tr><td style="padding:24px 32px 8px;">
                <a href="{{ $botonUrl }}" style="display:inline-block;background:#ED1C24;color:#ffffff;text-decoration:none;font-weight:bold;padding:13px 26px;border-radius:999px;">{{ $botonTexto ?? 'Abrir' }}</a>
            </td></tr>
            @endif
            <tr><td style="padding:24px 32px 28px;font-size:12px;color:#8a8a8a;">Recibes este correo porque tienes una cuenta en {{ config('cursos.nombre') }}.</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>

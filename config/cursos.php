<?php

return [
    // Nombre que aparece en el encabezado, los correos y el título de las páginas.
    'nombre' => env('APP_NAME', 'Academia de Marketing Digital'),

    // Correo que recibe el aviso cuando alguien nuevo se registra.
    // Si se deja vacío, se usa el remitente MAIL_FROM_ADDRESS.
    'correo_avisos' => env('ADMIN_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS')),

    // Porcentaje mínimo de respuestas correctas para aprobar el quiz de un módulo.
    'porcentaje_aprobacion' => (int) env('QUIZ_PASS_PERCENT', 60),

    // Tamaño máximo (en MB) de lo que se puede subir desde el administrador.
    // Ojo: el servidor (PHP) también tiene su propio límite; ver docs/DREAMHOST.md
    'max_video_mb' => (int) env('MAX_VIDEO_MB', 500),
    'max_archivo_mb' => (int) env('MAX_FILE_MB', 50),
];

# Academia de Marketing Digital

Plataforma de cursos en línea hecha con **Laravel 11 (PHP) + MySQL**, pensada para
alojarse en un hosting compartido como **DreamHost**. No necesita Node ni compilar nada:
los estilos están en `public/css/app.css`.

## ¿Qué hace?

**Para los alumnos**
- Se registran con nombre, correo y contraseña (teléfono opcional).
- Quedan **en revisión** hasta que el administrador les da acceso (reciben un correo al ser aprobados).
- Ven su **ruta de aprendizaje**: los módulos se desbloquean en orden; para abrir el siguiente hay que aprobar el quiz del anterior (o marcarlo como completado si no tiene quiz).
- Además hay **lecciones de consulta (libres)**, siempre abiertas.
- En cada módulo: video, notas, archivos descargables y un quiz de opción múltiple.
- Bandeja de **mensajes** dentro del sitio y recuperación de contraseña por correo.

**Para el administrador** (`/admin`)
- **Módulos**: crear N módulos, ordenarlos (↑ ↓), subir el video desde el navegador
  (o pegar un enlace de YouTube/Vimeo), escribir el quiz (hasta 3 preguntas, 4 opciones),
  subir archivos descargables, marcar como *libre* o *borrador*, y verlo “como alumno”.
- **Usuarios**: ver pendientes, **dar acceso**, **bloquear / desbloquear**, ver el avance de cada alumno,
  reiniciar su avance o eliminar la cuenta.
- **Mensajes**: escribir a una persona o a un grupo (aprobados, pendientes o todos);
  el mensaje queda en la bandeja del alumno y, si se marca, también se envía por correo.
- Aviso por correo cuando alguien nuevo se registra.

## Ajustes rápidos (archivo `.env`)

| Qué | Variable |
|---|---|
| Nombre del sitio | `APP_NAME` |
| Correo que envía los avisos | `MAIL_FROM_ADDRESS`, `MAIL_USERNAME`, `MAIL_PASSWORD` |
| Correo que recibe el aviso de nuevos registros | `ADMIN_NOTIFY_EMAIL` |
| % mínimo para aprobar un quiz | `QUIZ_PASS_PERCENT` (60 por defecto: 2 de 3 correctas) |
| Tamaño máximo de video / archivos | `MAX_VIDEO_MB`, `MAX_FILE_MB` |

Los **colores y tipografías** se cambian en un solo lugar: las variables al inicio de
`public/css/app.css`.

## Instalación

- En DreamHost: ver **[docs/DREAMHOST.md](docs/DREAMHOST.md)** (paso a paso).
- En una computadora (para desarrollo):

```bash
composer install
cp .env.example .env          # para probar rápido cambia DB_CONNECTION=sqlite y MAIL_MAILER=log
php artisan key:generate
php artisan migrate
php artisan cursos:crear-admin tu@correo.com
php artisan db:seed --class=DemoSeeder   # opcional: módulos y usuarios de ejemplo
php artisan serve
```

Usuarios del `DemoSeeder`: `admin@ejemplo.com / admin12345` y `alumno@ejemplo.com / alumno12345`.

## Pruebas

```bash
php artisan test
```

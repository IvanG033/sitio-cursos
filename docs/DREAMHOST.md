# Publicar el sitio en DreamHost (hosting compartido)

Esta guía asume un dominio ya alojado en DreamHost. Tiempo estimado: 30–45 minutos.

## 1. Preparar en el panel de DreamHost

1. **PHP 8.2 o superior**: *Websites → Manage Websites → (tu dominio) → Manage → PHP*.
2. **Carpeta pública**: en la misma pantalla, en *Web directory*, agrega `/public` al final
   (por ejemplo `cursos.tu-dominio.com/public`). Así solo es visible la carpeta `public`, y
   los videos, archivos y contraseñas quedan protegidos fuera de ella.
3. **Base de datos MySQL**: *MySQL Databases → Add New Database*. Anota el nombre de la base,
   el *hostname* (ej. `mysql.tu-dominio.com`), el usuario y la contraseña.
4. **Usuario con SSH**: *Websites → SFTP Users & Files*, edita el usuario y activa **SSH**.
   Se necesita para los comandos de instalación (sección 3).
5. **Correo**: si `ivan@mkt-innovacion.com` está en DreamHost, usa `smtp.dreamhost.com`
   (puerto 465). Si está en otro proveedor (Google Workspace, Outlook…), usa los datos SMTP de ese proveedor.

## 2. Subir los archivos

**Opción A – con SSH (recomendada)**

```bash
cd ~/cursos.tu-dominio.com
git clone https://github.com/IvanG033/sitio-cursos.git .
curl -sS https://getcomposer.org/installer | php
php composer.phar install --no-dev --optimize-autoloader
```

**Opción B – por FTP**

1. En GitHub, pestaña *Actions*, abre la última ejecución verde de
   “Pruebas y paquete para subir” y descarga **sitio-cursos-listo-para-subir**.
   Ese .zip ya incluye la carpeta `vendor`.
2. Descomprímelo y súbelo completo por FTP a la carpeta del dominio (ej. `cursos.tu-dominio.com/`).
3. Genera la clave `APP_KEY` (en tu computadora con `php artisan key:generate --show`, o pídela a quien te ayude con el sitio) y escríbela en `.env`.

## 3. Configurar

1. Copia `.env.example` como `.env` y llena: `APP_URL`, los datos `DB_*`, los `MAIL_*`
   (incluida la contraseña del correo), `ADMIN_NOTIFY_EMAIL` y una clave larga en `INSTALL_KEY`.
2. Crea las tablas y tu cuenta de administrador. Hay dos formas:

**Sin SSH (desde el navegador)**: abre `https://tu-dominio.com/instalar`, escribe la clave
`INSTALL_KEY` y tus datos, y pulsa *Instalar*. Listo. La misma página sirve más adelante para
aplicar los cambios de base de datos de una versión nueva. Si quieres desactivarla, deja
`INSTALL_KEY` vacía.

**Con SSH**, dentro de la carpeta del sitio:

```bash
php artisan key:generate
php artisan migrate --force
php artisan cursos:crear-admin ivan@mkt-innovacion.com --name="Iván"
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Permisos (normalmente ya están bien): `chmod -R 775 storage bootstrap/cache`.
4. Activa el certificado **SSL (Let’s Encrypt)** gratuito del dominio en el panel.

Listo: entra a `https://tu-dominio.com/entrar` con el administrador y empieza a crear módulos.

## 4. Permitir subir videos grandes

PHP limita el tamaño de lo que se sube desde el navegador. En DreamHost se cambia creando
el archivo `~/.php/8.2/phprc` (usa el número de tu versión de PHP) con:

```
upload_max_filesize = 512M
post_max_size = 520M
max_execution_time = 600
max_input_time = 600
memory_limit = 256M
```

Espera unos minutos a que PHP se reinicie. Ajusta también `MAX_VIDEO_MB` en `.env`
y vuelve a correr `php artisan config:cache`.

> **Consejo**: el hosting compartido no está pensado para que muchos alumnos vean video
> a la vez. Para videos largos o muchos alumnos conviene subirlos a **YouTube (como
> “No listado”) o Vimeo** y pegar el enlace en el módulo. Los videos subidos directamente
> solo los pueden ver alumnos con acceso.

## 5. Cambiar el correo más adelante

Edita en `.env`: `MAIL_FROM_ADDRESS`, `MAIL_USERNAME`, `MAIL_PASSWORD` (y `MAIL_HOST`/`MAIL_PORT`
si cambias de proveedor) y `ADMIN_NOTIFY_EMAIL`. Después ejecuta `php artisan config:cache`.

## 6. Actualizar el sitio cuando haya cambios

```bash
cd ~/cursos.tu-dominio.com
git pull
php composer.phar install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Respaldos

- Base de datos: *MySQL Databases* → phpMyAdmin → Exportar.
- Videos y archivos subidos: carpeta `storage/app/private`.

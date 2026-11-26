@extends('layouts.app')
@section('titulo', 'Instalación')
@section('fondo', 'decor-hero')
@section('contenido')
<div class="auth">
    <div class="tarjeta">
        <span class="antetitulo">Instalación</span>
        @if($installed)
            <h2>El sitio ya está instalado</h2>
            <p class="texto-suave">Si subiste una versión nueva del sitio, escribe la clave para aplicar los cambios a la base de datos.</p>
        @else
            <h2>Instalar el sitio</h2>
            <p class="texto-suave">Esto crea las tablas de la base de datos y tu cuenta de administrador. Solo se hace una vez.</p>
        @endif

        @if($dbError)
            <div class="aviso aviso-error">{{ $dbError }}</div>
        @endif

        <form method="POST" action="{{ route('install') }}">
            @csrf
            <div class="campo">
                <label for="clave">Clave de instalación</label>
                <input id="clave" type="password" name="clave" required autocomplete="off">
                <small>Está en el archivo .env, en la línea INSTALL_KEY.</small>
            </div>
            @unless($installed)
                <div class="campo">
                    <label for="name">Tu nombre</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="campo">
                    <label for="email">Tu correo (con este entrarás como administrador)</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div class="campo">
                    <label for="password">Contraseña de administrador</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password">
                    <small>Mínimo 8 caracteres.</small>
                </div>
                <div class="campo">
                    <label for="password_confirmation">Repite la contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
                </div>
            @endunless
            <button class="boton" type="submit" style="width:100%">{{ $installed ? 'Actualizar base de datos' : 'Instalar' }}</button>
        </form>
    </div>
</div>
@endsection

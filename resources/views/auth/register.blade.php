@extends('layouts.app')
@section('titulo', 'Registro')
@section('fondo', 'decor-hero')
@section('contenido')
<div class="auth">
    <div class="tarjeta">
        <span class="antetitulo">Inscripción</span>
        <h2>Crea tu cuenta</h2>
        <p class="texto-suave">Después de registrarte revisaremos tu solicitud y te avisaremos cuando tengas acceso.</p>
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="campo">
                <label for="name">Nombre completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            </div>
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
            </div>
            <div class="campo">
                <label for="phone">Teléfono / WhatsApp <span class="texto-suave">(opcional)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <small>Mínimo 8 caracteres.</small>
            </div>
            <div class="campo">
                <label for="password_confirmation">Repite la contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button class="boton" type="submit" style="width:100%">Crear mi cuenta</button>
        </form>
        <p class="centro mb-0 texto-suave" style="margin-top:20px">¿Ya tienes cuenta? <a href="{{ route('login') }}">Entra aquí</a></p>
    </div>
</div>
@endsection

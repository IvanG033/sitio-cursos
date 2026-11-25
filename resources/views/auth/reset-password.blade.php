@extends('layouts.app')
@section('titulo', 'Nueva contraseña')
@section('fondo', 'decor-hero')
@section('contenido')
<div class="auth">
    <div class="tarjeta">
        <h2>Crea una nueva contraseña</h2>
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
            </div>
            <div class="campo">
                <label for="password">Nueva contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password">
                <small>Mínimo 8 caracteres.</small>
            </div>
            <div class="campo">
                <label for="password_confirmation">Repite la contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            </div>
            <button class="boton" type="submit" style="width:100%">Guardar contraseña</button>
        </form>
    </div>
</div>
@endsection

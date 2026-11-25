@extends('layouts.app')
@section('titulo', 'Entrar')
@section('fondo', 'decor-hero')
@section('contenido')
<div class="auth">
    <div class="tarjeta">
        <span class="antetitulo">Bienvenido de nuevo</span>
        <h2>Entra a tu cuenta</h2>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>
            <div class="campo">
                <label class="casilla"><input type="checkbox" name="remember" value="1"> Mantener la sesión iniciada</label>
            </div>
            <button class="boton" type="submit" style="width:100%">Entrar</button>
        </form>
        <p class="centro" style="margin-top:20px"><a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a></p>
        <p class="centro mb-0 texto-suave">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate</a></p>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('titulo', 'Recuperar contraseña')
@section('fondo', 'decor-hero')
@section('contenido')
<div class="auth">
    <div class="tarjeta">
        <h2>¿Olvidaste tu contraseña?</h2>
        <p class="texto-suave">Escribe tu correo y te enviaremos un enlace para crear una nueva.</p>
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="campo">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <button class="boton" type="submit" style="width:100%">Enviar enlace</button>
        </form>
        <p class="centro mb-0" style="margin-top:20px"><a href="{{ route('login') }}">Volver a entrar</a></p>
    </div>
</div>
@endsection

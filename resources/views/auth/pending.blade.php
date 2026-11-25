@extends('layouts.app')
@section('titulo', 'Solicitud en revisión')
@section('contenido')
<div class="auth">
    <div class="tarjeta centro">
        <div class="paso">✓</div>
        <h2>¡Gracias por registrarte, {{ auth()->user()->name }}!</h2>
        <p>Tu solicitud está <strong>en revisión</strong>. En cuanto te demos acceso recibirás un correo en <strong>{{ auth()->user()->email }}</strong> y podrás ver los cursos.</p>
        <p class="texto-suave">Puedes cerrar esta página; vuelve a entrar cuando recibas el aviso.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="boton boton-secundario" type="submit">Cerrar sesión</button>
        </form>
    </div>
</div>
@endsection

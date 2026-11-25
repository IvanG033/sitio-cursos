<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('titulo')@yield('titulo') · @endif{{ config('cursos.nombre') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;0,800;1,600;1,700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('img/icono.png') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
</head>
<body>
@php($u = auth()->user())
<header class="encabezado">
    <div class="contenedor">
        <a class="marca" href="{{ route('home') }}" aria-label="{{ config('cursos.nombre') }} – inicio"><img src="{{ asset('img/logo.png') }}" alt="{{ config('cursos.nombre') }}" width="248" height="200"></a>
        <button class="menu-toggle" type="button" data-menu-toggle>Menú</button>
        <nav class="menu" data-menu>
            @guest
                <a href="{{ route('home') }}" @class(['activo' => request()->routeIs('home')])>Inicio</a>
                <a href="{{ route('login') }}" @class(['activo' => request()->routeIs('login')])>Entrar</a>
                <a class="boton boton-chico" href="{{ route('register') }}">Regístrate</a>
            @else
                @if($u->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" @class(['activo' => request()->routeIs('admin.*')])>Administrador</a>
                @endif
                @if($u->isApproved() || $u->isAdmin())
                    <a href="{{ route('dashboard') }}" @class(['activo' => request()->routeIs('dashboard', 'modules.*')])>Mis cursos</a>
                    @unless($u->isAdmin())
                        @php($sinLeer = $u->unreadMessagesCount())
                        <a href="{{ route('inbox.index') }}" @class(['activo' => request()->routeIs('inbox.*')])>Mensajes @if($sinLeer)<span class="insignia">{{ $sinLeer }}</span>@endif</a>
                    @endunless
                @endif
                <form class="en-linea" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="enlace" type="submit">Salir</button>
                </form>
            @endguest
        </nav>
    </div>
</header>

@if($u && $u->isAdmin() && request()->routeIs('admin.*'))
    <nav class="admin-barra">
        <div class="contenedor">
            <a href="{{ route('admin.dashboard') }}" @class(['activo' => request()->routeIs('admin.dashboard')])>Resumen</a>
            <a href="{{ route('admin.modules.index') }}" @class(['activo' => request()->routeIs('admin.modules.*')])>Módulos</a>
            <a href="{{ route('admin.users.index') }}" @class(['activo' => request()->routeIs('admin.users.*')])>Usuarios</a>
            <a href="{{ route('admin.messages.index') }}" @class(['activo' => request()->routeIs('admin.messages.*')])>Mensajes</a>
        </div>
    </nav>
@endif

<main class="@yield('fondo', 'decor-pagina')">
    @hasSection('sin-contenedor')
        @include('partials.flash', ['envolver' => true])
        @yield('contenido')
    @else
        <div class="contenedor seccion-chica">
            @include('partials.flash')
            @yield('contenido')
        </div>
    @endif
</main>

<footer class="pie">
    <div class="contenedor">
        <span style="display:flex;align-items:center;gap:14px"><img src="{{ asset('img/logo-blanco.png') }}" alt="" width="248" height="200"><span><strong>{{ config('cursos.nombre') }}</strong> · Todos los derechos reservados {{ date('Y') }}</span></span>
        <span>Aprende marketing digital paso a paso.</span>
    </div>
</footer>
<script src="{{ asset('js/app.js') }}?v=2"></script>
</body>
</html>

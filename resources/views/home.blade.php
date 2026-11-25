@extends('layouts.app')
@section('sin-contenedor', true)

@section('contenido')
<section class="hero">
    <div class="contenedor hero-grid">
        <div>
            <span class="antetitulo">Escuela de marketing digital</span>
            <h1>No necesitas saberlo todo. Necesitas saber <span class="acento">qué aprender primero.</span></h1>
            <p class="lead">Un programa paso a paso, con videos cortos, ejercicios descargables y quizzes para que pongas en práctica cada lección en tu negocio.</p>
            <div class="botones">
                @auth
                    <a class="boton" href="{{ route('dashboard') }}">Ir a mis cursos</a>
                @else
                    <a class="boton" href="{{ route('register') }}">Quiero inscribirme</a>
                    <a class="boton boton-secundario" href="{{ route('login') }}">Ya tengo cuenta</a>
                @endauth
            </div>
        </div>
        <div class="hero-tarjeta">
            <span class="antetitulo" style="color:#ffb8a6">Lo que obtienes</span>
            <h3>Aprende a tu ritmo, con método</h3>
            <ul class="lista-check">
                <li>Lecciones en video, en orden lógico</li>
                <li>Quiz rápido al final de cada módulo</li>
                <li>Plantillas y ejercicios para descargar</li>
                <li>Lecciones extra de consulta, siempre abiertas</li>
            </ul>
        </div>
    </div>
</section>

<section class="seccion seccion-blanca">
    <div class="contenedor">
        <span class="antetitulo">Cómo funciona</span>
        <h2>Cada módulo construye sobre el anterior</h2>
        <p class="lead">Avanzas cuando dominas el tema. Así no te saltas lo importante.</p>
        <div class="rejilla" style="margin-top:32px">
            <div class="tarjeta tarjeta-plana"><div class="paso">01</div><h3>Regístrate</h3><p class="mb-0">Crea tu cuenta en un minuto. Revisamos tu solicitud y te damos acceso.</p></div>
            <div class="tarjeta tarjeta-plana"><div class="paso">02</div><h3>Mira la lección</h3><p class="mb-0">Videos claros y directos, con material de apoyo para practicar.</p></div>
            <div class="tarjeta tarjeta-plana"><div class="paso">03</div><h3>Refuerza con el quiz</h3><p class="mb-0">Dos o tres preguntas para confirmar lo aprendido y desbloquear el siguiente módulo.</p></div>
            <div class="tarjeta tarjeta-plana"><div class="paso">04</div><h3>Aplica en tu negocio</h3><p class="mb-0">Descarga los ejercicios y lleva cada tema a la práctica.</p></div>
        </div>
    </div>
</section>

@if($modules->isNotEmpty())
<section class="seccion">
    <div class="contenedor contenedor-angosto">
        <span class="antetitulo">Temario</span>
        <h2>Lo que vas a aprender</h2>
        @php($n = 0)
        @foreach($modules as $m)
            <div class="modulo">
                <div class="modulo-num">{{ $m->is_free ? '★' : ++$n }}</div>
                <div class="modulo-info">
                    <h3>{{ $m->title }}</h3>
                    @if($m->summary)<p>{{ $m->summary }}</p>@endif
                </div>
                @if($m->is_free)<span class="etiqueta etiqueta-acento">Consulta libre</span>@endif
            </div>
        @endforeach
    </div>
</section>
@endif

<section class="seccion seccion-oscura">
    <div class="contenedor centro">
        <span class="antetitulo">Crecemos contigo</span>
        <h2>¿Listo para empezar?</h2>
        <p class="lead" style="margin:0 auto 24px;color:#cfdbe3">Regístrate y en cuanto aprobemos tu acceso podrás comenzar con el primer módulo.</p>
        @guest<a class="boton" href="{{ route('register') }}">Crear mi cuenta</a>@else<a class="boton" href="{{ route('dashboard') }}">Ir a mis cursos</a>@endguest
    </div>
</section>
@endsection

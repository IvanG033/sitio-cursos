@extends('layouts.app')
@section('sin-contenedor', true)
@section('fondo', '')

@section('contenido')
<section class="banda decor-hero hero">
    <div class="contenedor hero-grid">
        <div>
            <span class="antetitulo">Escuela de marketing digital</span>
            <h1>Aprende a hacer <span class="resaltado">crecer</span> tu negocio con marketing digital.</h1>
            <span class="franja-texto">Lecciones en video. Ejercicios prácticos. Avanza a tu ritmo.</span>
            <div class="botones">
                @auth
                    <a class="boton" href="{{ route('dashboard') }}">Ir a mis cursos</a>
                @else
                    <a class="boton" href="{{ route('register') }}">Quiero inscribirme</a>
                    <a class="boton boton-blanco-borde" href="{{ route('login') }}">Ya tengo cuenta</a>
                @endauth
            </div>
        </div>
        <div class="hero-visual" aria-hidden="true">
            <div class="mancha"><img src="{{ asset('img/logo.png') }}" alt="" width="248" height="200"></div>
        </div>
    </div>
</section>

<section class="banda banda-roja frase">
    <div class="contenedor">
        <p>No necesitas saberlo todo. <em>Necesitas saber qué aprender primero.</em></p>
    </div>
</section>

<section class="banda banda-azul decor-azul seccion">
    <div class="contenedor">
        <div class="centro">
            <span class="antetitulo">Cómo funciona</span>
            <h2>Cada módulo construye sobre el anterior</h2>
            <p class="lead" style="margin:0 auto;color:#cfd9e2">Avanzas cuando dominas el tema. Así no te saltas lo importante.</p>
        </div>
        <div class="rejilla" style="margin-top:36px">
            <div class="tarjeta-oscura"><div class="paso">01</div><h3>Regístrate</h3><p class="mb-0">Crea tu cuenta en un minuto. Revisamos tu solicitud y te damos acceso.</p></div>
            <div class="tarjeta-oscura"><div class="paso">02</div><h3>Mira la lección</h3><p class="mb-0">Videos claros y directos, con material de apoyo para practicar.</p></div>
            <div class="tarjeta-oscura"><div class="paso">03</div><h3>Refuerza con el quiz</h3><p class="mb-0">Dos o tres preguntas para confirmar lo aprendido y desbloquear el siguiente módulo.</p></div>
            <div class="tarjeta-oscura"><div class="paso">04</div><h3>Aplica en tu negocio</h3><p class="mb-0">Descarga los ejercicios y lleva cada tema a la práctica.</p></div>
        </div>
        <div class="centro" style="margin-top:36px">
            <span class="boton boton-amarillo" style="cursor:default">Tu negocio. Tu momento. Tu estrategia.</span>
        </div>
    </div>
</section>

<section class="banda decor-crema seccion">
    <div class="contenedor rejilla-2">
        <div>
            <span class="antetitulo">Lo que obtienes</span>
            <h2>Aprende con método, no con prisa</h2>
            <ul class="lista-check">
                <li>Lecciones en video, en orden lógico</li>
                <li>Quiz rápido al final de cada módulo</li>
                <li>Plantillas y ejercicios para descargar</li>
                <li>Lecciones extra de consulta, siempre abiertas</li>
                <li>Mensajes directos de tu instructor</li>
            </ul>
        </div>
        @if($modules->isNotEmpty())
        <div>
            <span class="antetitulo">Temario</span>
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
        @endif
    </div>
</section>

<section class="banda decor-cta seccion" style="padding-top:64px;padding-bottom:64px">
    <div class="contenedor centro">
        <span class="antetitulo">Crecemos contigo</span>
        <h2>¿Listo para empezar?</h2>
        <p class="lead" style="margin:0 auto 24px;color:var(--color-secundario)">Regístrate y en cuanto aprobemos tu acceso podrás comenzar con el primer módulo.</p>
        @guest<a class="boton" href="{{ route('register') }}">Crear mi cuenta</a>@else<a class="boton" href="{{ route('dashboard') }}">Ir a mis cursos</a>@endguest
    </div>
</section>
@endsection

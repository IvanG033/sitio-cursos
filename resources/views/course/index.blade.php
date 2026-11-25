@extends('layouts.app')
@section('titulo', 'Mis cursos')
@section('contenido')
<div class="titulo-pagina">
    <div>
        <span class="antetitulo">Hola, {{ auth()->user()->name }}</span>
        <h1>Tu ruta de aprendizaje</h1>
    </div>
    @if($next)
        <a class="boton" href="{{ route('modules.show', $next['module']) }}">Continuar: {{ \Illuminate\Support\Str::limit($next['module']->title, 32) }} →</a>
    @endif
</div>

@if($main->isNotEmpty())
    <div class="tarjeta tarjeta-plana" style="margin-bottom:28px">
        <div style="display:flex;justify-content:space-between;margin-bottom:8px">
            <strong>Tu avance</strong>
            <span class="texto-suave">{{ $done }} de {{ $main->count() }} módulos · {{ $percent }}%</span>
        </div>
        <div class="barra"><span style="width: {{ $percent }}%"></span></div>
    </div>
@endif

<div class="rejilla-2">
    <section>
        <h2 style="font-size:1.3rem">Ruta principal</h2>
        <p class="texto-suave">Se desbloquean en orden: completa cada módulo para abrir el siguiente.</p>
        @forelse($main as $i => $e)
            @include('course._item', ['e' => $e, 'num' => $i + 1])
        @empty
            <div class="tarjeta tarjeta-plana vacio">Todavía no hay módulos publicados. ¡Vuelve pronto!</div>
        @endforelse
    </section>

    <section>
        <h2 style="font-size:1.3rem">Lecciones de consulta</h2>
        <p class="texto-suave">Siempre abiertas. Úsalas cuando quieras repasar o profundizar.</p>
        @forelse($free as $e)
            @include('course._item', ['e' => $e, 'num' => '★'])
        @empty
            <div class="tarjeta tarjeta-plana vacio">Aún no hay lecciones de consulta.</div>
        @endforelse
    </section>
</div>
@endsection

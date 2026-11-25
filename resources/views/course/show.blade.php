@extends('layouts.app')
@section('titulo', $module->title)
@section('contenido')
@php($completed = $progress?->completed_at)
<p><a href="{{ route('dashboard') }}">← Volver a mis cursos</a></p>

<div class="titulo-pagina">
    <div>
        <span class="antetitulo">{{ $module->is_free ? 'Lección de consulta' : 'Módulo' }}</span>
        <h1>{{ $module->title }}</h1>
        @if($module->summary)<p class="lead mb-0">{{ $module->summary }}</p>@endif
    </div>
    @if($completed)<span class="etiqueta etiqueta-exito">✓ Completado</span>@endif
</div>

@if(auth()->user()->isAdmin() && !$module->is_published)
    <div class="aviso aviso-info">Vista previa: este módulo es un borrador y los alumnos todavía no lo ven.</div>
@endif

<div class="rejilla-2 principal">
    <div>
        @if($module->video_path)
            <div class="video">
                <video controls controlsList="nodownload" preload="metadata" src="{{ route('modules.video', $module) }}"></video>
            </div>
        @elseif($module->embedUrl())
            <div class="video">
                <iframe src="{{ $module->embedUrl() }}" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen title="{{ $module->title }}"></iframe>
            </div>
        @elseif($module->video_url)
            <div class="tarjeta"><a class="boton" href="{{ $module->video_url }}" target="_blank" rel="noopener">Ver el video de la lección</a></div>
        @endif

        @if($module->content)
            <div class="tarjeta tarjeta-plana" style="margin-top:24px">
                <h3>Notas de la lección</h3>
                <div class="contenido-texto">{{ $module->content }}</div>
            </div>
        @endif

        <div id="quiz" class="tarjeta" style="margin-top:24px">
            @if($module->questions->isNotEmpty())
                <span class="antetitulo">Refuerza lo aprendido</span>
                <h2 style="font-size:1.4rem">Quiz del módulo</h2>

                @if($result)
                    @if($result['passed'])
                        <div class="aviso aviso-exito">¡Muy bien! Acertaste {{ $result['correct'] }} de {{ $result['total'] }}. @unless($module->is_free)El siguiente módulo ya está desbloqueado.@endunless</div>
                    @else
                        <div class="aviso aviso-error">Acertaste {{ $result['correct'] }} de {{ $result['total'] }}. Necesitas al menos {{ config('cursos.porcentaje_aprobacion') }}% para avanzar. Repasa la lección e inténtalo de nuevo.</div>
                    @endif
                @elseif($completed)
                    <div class="aviso aviso-exito">Ya aprobaste este quiz (mejor resultado: {{ $progress->best_score }}%). Puedes volver a contestarlo para repasar.</div>
                @else
                    <p class="texto-suave">Contesta {{ $module->questions->count() === 1 ? 'la pregunta' : 'las '.$module->questions->count().' preguntas' }} para completar el módulo.</p>
                @endif

                <form method="POST" action="{{ route('modules.quiz', $module) }}">
                    @csrf
                    @foreach($module->questions as $qi => $q)
                        @php($d = $result['detail'][$q->id] ?? null)
                        <div class="pregunta">
                            <fieldset>
                                <legend>{{ $qi + 1 }}. {{ $q->text }}</legend>
                                @foreach($q->options as $o)
                                    @php($elegida = $d ? $d['chosen'] === $o->id : (int) old('answers.'.$q->id) === $o->id)
                                    <label @class(['opcion', 'correcta' => $d && $elegida && $d['ok'], 'incorrecta' => $d && $elegida && !$d['ok']])>
                                        <input type="radio" name="answers[{{ $q->id }}]" value="{{ $o->id }}" @checked($elegida) required>
                                        <span>{{ $o->text }}</span>
                                    </label>
                                @endforeach
                            </fieldset>
                        </div>
                    @endforeach
                    <button class="boton" type="submit">{{ $result ? 'Intentar de nuevo' : 'Enviar respuestas' }}</button>
                </form>
            @else
                <h2 style="font-size:1.3rem">¿Terminaste la lección?</h2>
                @if($completed)
                    <p class="mb-0">Ya marcaste este módulo como completado. 🎉</p>
                @else
                    <p class="texto-suave">Márcala como completada para avanzar.</p>
                    <form method="POST" action="{{ route('modules.complete', $module) }}">
                        @csrf
                        <button class="boton boton-exito" type="submit">Marcar como completado</button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <aside>
        <div class="tarjeta tarjeta-plana">
            <h3>Material descargable</h3>
            @forelse($module->files as $f)
                <div class="archivo">
                    <div class="archivo-ext">{{ \Illuminate\Support\Str::limit($f->extension(), 4, '') }}</div>
                    <div class="archivo-info">
                        <strong>{{ $f->title }}</strong>
                        <small>{{ $f->humanSize() }}</small>
                    </div>
                    <a class="boton boton-chico boton-secundario" href="{{ route('modules.download', [$module, $f]) }}">Descargar</a>
                </div>
            @empty
                <p class="texto-suave mb-0">Este módulo no tiene archivos para descargar.</p>
            @endforelse
        </div>

        @if($nextEntry)
            <div class="tarjeta tarjeta-plana" style="margin-top:20px">
                <span class="antetitulo">Siguiente módulo</span>
                <h3>{{ $nextEntry['module']->title }}</h3>
                @if($nextEntry['unlocked'])
                    <a class="boton" href="{{ route('modules.show', $nextEntry['module']) }}">Continuar →</a>
                @else
                    <p class="texto-suave mb-0">🔒 Se desbloquea al completar este módulo.</p>
                @endif
            </div>
        @endif
    </aside>
</div>
@endsection

{{-- Un módulo en la lista del alumno. Variables: $e (module, unlocked, completed), $num --}}
@php($m = $e['module'])
@if($e['unlocked'])
    <a class="modulo {{ $e['completed'] ? 'completado' : '' }}" href="{{ route('modules.show', $m) }}">
@else
    <div class="modulo bloqueado" title="Completa el módulo anterior para desbloquearlo">
@endif
    <div class="modulo-num">@if($e['completed'])✓@elseif(!$e['unlocked'])🔒@else{{ $num }}@endif</div>
    <div class="modulo-info">
        <h3>{{ $m->title }}</h3>
        @if($m->summary)<p>{{ $m->summary }}</p>@endif
    </div>
    @if($e['completed'])
        <span class="etiqueta etiqueta-exito">Completado</span>
    @elseif(!$e['unlocked'])
        <span class="etiqueta">Bloqueado</span>
    @elseif($m->is_free)
        <span class="etiqueta etiqueta-acento">Libre</span>
    @else
        <span class="etiqueta etiqueta-alerta">Disponible</span>
    @endif
@if($e['unlocked'])</a>@else</div>@endif

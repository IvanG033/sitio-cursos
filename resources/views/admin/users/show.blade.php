@extends('layouts.app')
@section('titulo', $user->name)
@section('contenido')
<p><a href="{{ route('admin.users.index') }}">← Usuarios</a></p>
<div class="titulo-pagina">
    <div>
        <span class="antetitulo">{{ $user->statusLabel() }}</span>
        <h1>{{ $user->name }}</h1>
        <p class="texto-suave mb-0">{{ $user->email }}@if($user->phone) · {{ $user->phone }}@endif · Registrado el {{ $user->created_at->format('d/m/Y') }}</p>
    </div>
    @include('admin.users._actions', ['user' => $user])
</div>

<div class="rejilla-2">
    <section>
        <h2 style="font-size:1.3rem">Avance por módulo</h2>
        <div class="tabla-envoltura">
            <table class="tabla">
                <thead><tr><th>Módulo</th><th>Estado</th><th>Intentos</th><th>Mejor</th></tr></thead>
                <tbody>
                @forelse($roadmap as $e)
                    @php($p = $progress[$e['module']->id] ?? null)
                    <tr>
                        <td>{{ $e['module']->title }} @if($e['module']->is_free)<span class="etiqueta etiqueta-acento">Libre</span>@endif</td>
                        <td>
                            @if($e['completed'])<span class="etiqueta etiqueta-exito">Completado</span>
                            @elseif($e['unlocked'])<span class="etiqueta etiqueta-alerta">Disponible</span>
                            @else<span class="etiqueta">Bloqueado</span>@endif
                        </td>
                        <td>{{ $p->attempts ?? 0 }}</td>
                        <td>{{ $p ? $p->best_score.'%' : '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="texto-suave">No hay módulos publicados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="botones" style="margin-top:16px">
            <form class="en-linea" method="POST" action="{{ route('admin.users.reset', $user) }}">@csrf<button class="boton boton-chico boton-secundario" type="submit" data-confirmar="¿Borrar todo el avance de {{ $user->name }}? Tendrá que empezar desde el primer módulo.">Reiniciar avance</button></form>
            <form class="en-linea" method="POST" action="{{ route('admin.users.destroy', $user) }}">@csrf @method('DELETE')<button class="boton boton-chico boton-peligro" type="submit" data-confirmar="¿Eliminar definitivamente la cuenta de {{ $user->name }}? Esto no se puede deshacer.">Eliminar cuenta</button></form>
        </div>
    </section>
    <section>
        <h2 style="font-size:1.3rem">Mensajes enviados</h2>
        @forelse($messages as $m)
            <div class="tarjeta tarjeta-plana" style="margin-bottom:10px;padding:16px">
                <strong>{{ $m->subject }}</strong>
                <div class="texto-suave" style="font-size:.85rem">{{ $m->created_at->format('d/m/Y H:i') }} · {{ $m->read_at ? 'Leído' : 'Sin leer' }}{{ $m->emailed_at ? ' · Enviado por correo' : '' }}</div>
            </div>
        @empty
            <p class="texto-suave">Todavía no le has enviado mensajes.</p>
        @endforelse
        <a class="boton boton-chico" href="{{ route('admin.messages.create', ['para' => $user->id]) }}">Escribir mensaje</a>
    </section>
</div>
@endsection

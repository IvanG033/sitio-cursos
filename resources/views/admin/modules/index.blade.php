@extends('layouts.app')
@section('titulo', 'Módulos')
@section('contenido')
<div class="titulo-pagina">
    <div><span class="antetitulo">Administrador</span><h1>Módulos</h1></div>
    <a class="boton" href="{{ route('admin.modules.create') }}">+ Nuevo módulo</a>
</div>
<p class="texto-suave">El orden de esta lista es el orden en que los alumnos avanzan. Los módulos <strong>de la ruta</strong> se desbloquean uno tras otro; los <strong>libres</strong> están siempre abiertos.</p>

@if($modules->isEmpty())
    <div class="tarjeta tarjeta-plana vacio">Aún no hay módulos. <a href="{{ route('admin.modules.create') }}">Crea el primero</a>.</div>
@else
<div class="tabla-envoltura">
    <table class="tabla">
        <thead><tr><th>Orden</th><th>Módulo</th><th>Tipo</th><th>Contenido</th><th>Estado</th><th></th></tr></thead>
        <tbody>
        @foreach($modules as $m)
            <tr>
                <td style="white-space:nowrap">
                    <form class="en-linea" method="POST" action="{{ route('admin.modules.move', $m) }}">@csrf<input type="hidden" name="direction" value="up"><button class="boton boton-chico boton-secundario" type="submit" title="Subir" @disabled($loop->first)>↑</button></form>
                    <form class="en-linea" method="POST" action="{{ route('admin.modules.move', $m) }}">@csrf<input type="hidden" name="direction" value="down"><button class="boton boton-chico boton-secundario" type="submit" title="Bajar" @disabled($loop->last)>↓</button></form>
                </td>
                <td><a href="{{ route('admin.modules.edit', $m) }}"><strong>{{ $m->title }}</strong></a></td>
                <td>@if($m->is_free)<span class="etiqueta etiqueta-acento">Libre</span>@else<span class="etiqueta">Ruta</span>@endif</td>
                <td class="texto-suave" style="font-size:.85rem">
                    {{ $m->hasVideo() ? '🎬 Video' : 'Sin video' }} · {{ $m->questions_count }} preg. · {{ $m->files_count }} arch.
                </td>
                <td>@if($m->is_published)<span class="etiqueta etiqueta-exito">Publicado</span>@else<span class="etiqueta etiqueta-alerta">Borrador</span>@endif</td>
                <td class="acciones">
                    <div class="botones">
                        <a class="boton boton-chico boton-secundario" href="{{ route('modules.show', $m) }}">Ver</a>
                        <a class="boton boton-chico" href="{{ route('admin.modules.edit', $m) }}">Editar</a>
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

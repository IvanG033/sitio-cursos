@extends('layouts.app')
@section('titulo', 'Mensajes')
@section('contenido')
<div class="titulo-pagina"><h1>Mensajes</h1></div>
@if($messages->isEmpty())
    <div class="tarjeta tarjeta-plana vacio">No tienes mensajes todavía.</div>
@else
    <div class="tabla-envoltura">
        <table class="tabla">
            <thead><tr><th>Asunto</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            @foreach($messages as $m)
                <tr @class(['no-leido' => !$m->read_at])>
                    <td>@if(!$m->read_at)<span class="etiqueta etiqueta-acento">Nuevo</span> @endif{{ $m->subject }}</td>
                    <td class="texto-suave">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td class="acciones"><a class="boton boton-chico boton-secundario" href="{{ route('inbox.show', $m) }}">Leer</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
@endif
@endsection

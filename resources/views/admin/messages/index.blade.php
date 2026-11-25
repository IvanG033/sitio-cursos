@extends('layouts.app')
@section('titulo', 'Mensajes')
@section('contenido')
<div class="titulo-pagina">
    <div><span class="antetitulo">Administrador</span><h1>Mensajes enviados</h1></div>
    <a class="boton" href="{{ route('admin.messages.create') }}">+ Nuevo mensaje</a>
</div>
@if($messages->isEmpty())
    <div class="tarjeta tarjeta-plana vacio">Todavía no has enviado mensajes.</div>
@else
    <div class="tabla-envoltura">
        <table class="tabla">
            <thead><tr><th>Para</th><th>Asunto</th><th>Fecha</th><th>Estado</th></tr></thead>
            <tbody>
            @foreach($messages as $m)
                <tr>
                    <td>@if($m->user)<a href="{{ route('admin.users.show', $m->user) }}">{{ $m->user->name }}</a>@else—@endif</td>
                    <td>{{ $m->subject }}</td>
                    <td class="texto-suave">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <span @class(['etiqueta', 'etiqueta-exito' => $m->read_at])>{{ $m->read_at ? 'Leído' : 'Sin leer' }}</span>
                        @if($m->emailed_at)<span class="etiqueta">✉ Correo</span>@endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $messages->links() }}
@endif
@endsection

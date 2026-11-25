@extends('layouts.app')
@section('titulo', 'Administrador')
@section('contenido')
<div class="titulo-pagina">
    <div><span class="antetitulo">Administrador</span><h1>Resumen</h1></div>
    <div class="botones">
        <a class="boton" href="{{ route('admin.modules.create') }}">+ Nuevo módulo</a>
        <a class="boton boton-secundario" href="{{ route('admin.messages.create') }}">Enviar mensaje</a>
    </div>
</div>

<div class="cifras">
    <a class="cifra" href="{{ route('admin.users.index', ['estado' => 'pending']) }}"><strong>{{ $pendingCount }}</strong><span>Solicitudes pendientes</span></a>
    <a class="cifra" href="{{ route('admin.users.index', ['estado' => 'approved']) }}"><strong>{{ $approvedCount }}</strong><span>Alumnos con acceso</span></a>
    <a class="cifra" href="{{ route('admin.users.index', ['estado' => 'blocked']) }}"><strong>{{ $blockedCount }}</strong><span>Bloqueados</span></a>
    <a class="cifra" href="{{ route('admin.modules.index') }}"><strong>{{ $moduleCount }}</strong><span>Módulos</span></a>
    <div class="cifra"><strong>{{ $completions }}</strong><span>Módulos completados (total)</span></div>
</div>

<h2 style="font-size:1.3rem">Solicitudes de acceso pendientes</h2>
@if($pending->isEmpty())
    <div class="tarjeta tarjeta-plana vacio">No hay solicitudes pendientes. 👌</div>
@else
    @include('admin.users._table', ['users' => $pending, 'totalModules' => null])
@endif
@endsection

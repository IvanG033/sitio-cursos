@extends('layouts.app')
@section('titulo', 'Usuarios')
@section('contenido')
<div class="titulo-pagina">
    <div><span class="antetitulo">Administrador</span><h1>Usuarios</h1></div>
    <a class="boton boton-secundario" href="{{ route('admin.messages.create') }}">Enviar mensaje</a>
</div>

<div class="filtros">
    <a @class(['etiqueta', 'etiqueta-acento' => !$estado]) href="{{ route('admin.users.index') }}">Todos</a>
    @foreach(\App\Models\User::STATUS_LABELS as $key => $label)
        <a @class(['etiqueta', 'etiqueta-acento' => $estado === $key]) href="{{ route('admin.users.index', ['estado' => $key]) }}">{{ $label }}</a>
    @endforeach
    <form method="GET" action="{{ route('admin.users.index') }}">
        @if($estado)<input type="hidden" name="estado" value="{{ $estado }}">@endif
        <input type="search" name="buscar" value="{{ $buscar }}" placeholder="Buscar nombre o correo">
        <button class="boton boton-chico" type="submit">Buscar</button>
    </form>
</div>

@if($users->isEmpty())
    <div class="tarjeta tarjeta-plana vacio">No hay usuarios con ese filtro.</div>
@else
    @include('admin.users._table', ['users' => $users, 'totalModules' => $totalModules])
    {{ $users->links() }}
@endif
@endsection

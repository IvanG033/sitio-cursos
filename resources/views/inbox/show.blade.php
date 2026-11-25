@extends('layouts.app')
@section('titulo', $message->subject)
@section('contenido')
<div class="contenedor-angosto" style="margin:0 auto">
    <p><a href="{{ route('inbox.index') }}">← Todos los mensajes</a></p>
    <div class="tarjeta">
        <span class="antetitulo">{{ $message->created_at->format('d/m/Y H:i') }}</span>
        <h2>{{ $message->subject }}</h2>
        <div class="contenido-texto">{{ $message->body }}</div>
    </div>
</div>
@endsection

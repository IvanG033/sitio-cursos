@extends('layouts.app')
@section('titulo', 'Nuevo mensaje')
@section('contenido')
<div class="contenedor-angosto" style="margin:0 auto">
    <p><a href="{{ route('admin.messages.index') }}">← Mensajes</a></p>
    <div class="tarjeta">
        <span class="antetitulo">Administrador</span>
        <h2>Nuevo mensaje</h2>
        <form method="POST" action="{{ route('admin.messages.store') }}">
            @csrf
            <div class="campo">
                <label for="to">Para</label>
                @php($para = old('to', $selected))
                <select id="to" name="to" required>
                    <option value="">Elige…</option>
                    <optgroup label="Grupos">
                        <option value="approved" @selected($para === 'approved')>Todos los alumnos con acceso</option>
                        <option value="pending" @selected($para === 'pending')>Todos los pendientes de aprobación</option>
                        <option value="all" @selected($para === 'all')>Todos (menos bloqueados)</option>
                    </optgroup>
                    <optgroup label="Una persona">
                        @foreach($students as $s)
                            <option value="{{ $s->id }}" @selected((string) $para === (string) $s->id)>{{ $s->name }} — {{ $s->email }} ({{ $s->statusLabel() }})</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>
            <div class="campo">
                <label for="subject">Asunto</label>
                <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required maxlength="180">
            </div>
            <div class="campo">
                <label for="body">Mensaje</label>
                <textarea id="body" name="body" rows="8" required>{{ old('body') }}</textarea>
            </div>
            <div class="campo">
                <label class="casilla">
                    <input type="hidden" name="send_email" value="0">
                    <input type="checkbox" name="send_email" value="1" @checked(old('send_email', true))>
                    <span>Enviar también por correo electrónico<br><small class="texto-suave">El mensaje siempre queda en la bandeja del alumno dentro del sitio.</small></span>
                </label>
            </div>
            <button class="boton" type="submit">Enviar mensaje</button>
        </form>
    </div>
</div>
@endsection

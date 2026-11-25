@extends('layouts.app')
@section('titulo', $module->exists ? 'Editar módulo' : 'Nuevo módulo')
@section('contenido')
@php
    // Siempre mostramos 3 preguntas con 4 opciones; las que queden vacías se ignoran.
    $preguntas = old('questions') ?? $module->questions->map(fn ($q) => [
        'text' => $q->text,
        'options' => $q->options->pluck('text')->all(),
        'correct' => $q->options->search(fn ($o) => $o->is_correct),
    ])->all();
@endphp
<p><a href="{{ route('admin.modules.index') }}">← Módulos</a></p>
<div class="titulo-pagina">
    <div><span class="antetitulo">Administrador</span><h1>{{ $module->exists ? $module->title : 'Nuevo módulo' }}</h1></div>
    @if($module->exists)<a class="boton boton-secundario" href="{{ route('modules.show', $module) }}">Ver como alumno</a>@endif
</div>

<form method="POST" enctype="multipart/form-data" action="{{ $module->exists ? route('admin.modules.update', $module) : route('admin.modules.store') }}">
    @csrf
    @if($module->exists) @method('PUT') @endif

    <div class="rejilla-2 principal">
        <div>
            <div class="tarjeta tarjeta-plana">
                <h3>1. Información</h3>
                <div class="campo">
                    <label for="title">Título del módulo</label>
                    <input id="title" type="text" name="title" value="{{ old('title', $module->title) }}" required>
                </div>
                <div class="campo">
                    <label for="summary">Resumen corto</label>
                    <input id="summary" type="text" name="summary" value="{{ old('summary', $module->summary) }}" maxlength="500">
                    <small>Aparece en la lista de módulos.</small>
                </div>
                <div class="campo mb-0">
                    <label for="content">Notas de la lección <span class="texto-suave">(opcional)</span></label>
                    <textarea id="content" name="content" rows="6">{{ old('content', $module->content) }}</textarea>
                </div>
            </div>

            <div class="tarjeta tarjeta-plana" style="margin-top:20px">
                <h3>2. Video de la lección</h3>
                @if($module->video_path)
                    <div class="aviso aviso-exito" style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                        <span>🎬 Video subido: {{ $module->video_original_name }}</span>
                    </div>
                @endif
                <div class="campo">
                    <label for="video">{{ $module->video_path ? 'Reemplazar video' : 'Subir video' }}</label>
                    <input id="video" type="file" name="video" accept="video/mp4,video/webm,video/quicktime,video/ogg">
                    <small>MP4 recomendado. Máximo {{ config('cursos.max_video_mb') }} MB (y lo que permita el hosting). Para videos muy pesados es mejor usar un enlace de YouTube (oculto) o Vimeo.</small>
                </div>
                <div class="campo mb-0">
                    <label for="video_url">…o pega un enlace de YouTube / Vimeo</label>
                    <input id="video_url" type="url" name="video_url" value="{{ old('video_url', $module->video_url) }}" placeholder="https://youtu.be/...">
                    <small>Si hay un video subido, se muestra ese primero.</small>
                </div>
            </div>

            <div class="tarjeta tarjeta-plana" style="margin-top:20px">
                <h3>3. Quiz (2 o 3 preguntas)</h3>
                <p class="texto-suave">Escribe la pregunta y sus opciones, y marca con el círculo la respuesta correcta. Deja en blanco las preguntas que no uses. Si el módulo no tiene quiz, el alumno lo completa con un botón “Marcar como completado”.</p>
                @for($i = 0; $i < 3; $i++)
                    @php($p = $preguntas[$i] ?? ['text' => '', 'options' => [], 'correct' => null])
                    <div class="bloque-pregunta">
                        <div class="campo">
                            <label for="q{{ $i }}">Pregunta {{ $i + 1 }}</label>
                            <input id="q{{ $i }}" type="text" name="questions[{{ $i }}][text]" value="{{ $p['text'] ?? '' }}">
                        </div>
                        <span class="etiqueta-campo" style="font-size:.85rem">Opciones (● = correcta)</span>
                        @for($j = 0; $j < 4; $j++)
                            <div class="opcion-admin">
                                <input type="radio" name="questions[{{ $i }}][correct]" value="{{ $j }}" title="Respuesta correcta" @checked(isset($p['correct']) && $p['correct'] !== false && (string) $p['correct'] === (string) $j)>
                                <input type="text" name="questions[{{ $i }}][options][{{ $j }}]" value="{{ $p['options'][$j] ?? '' }}" placeholder="Opción {{ chr(65 + $j) }}">
                            </div>
                        @endfor
                    </div>
                @endfor
            </div>
        </div>

        <div>
            <div class="tarjeta tarjeta-plana">
                <h3>Configuración</h3>
                <div class="campo">
                    <label class="casilla">
                        <input type="hidden" name="is_published" value="0">
                        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $module->is_published))>
                        <span>Publicado<br><small class="texto-suave">Si no está marcado, es un borrador que los alumnos no ven.</small></span>
                    </label>
                </div>
                <div class="campo mb-0">
                    <label class="casilla">
                        <input type="hidden" name="is_free" value="0">
                        <input type="checkbox" name="is_free" value="1" @checked(old('is_free', $module->is_free))>
                        <span>Módulo libre (siempre desbloqueado)<br><small class="texto-suave">Para lecciones alternas o de consulta. No bloquea ni cuenta en la ruta principal.</small></span>
                    </label>
                </div>
            </div>

            <div class="tarjeta tarjeta-plana" style="margin-top:20px">
                <h3>4. Archivos descargables</h3>
                @if($module->exists && $module->files->isNotEmpty())
                    @foreach($module->files as $f)
                        <div class="archivo">
                            <div class="archivo-ext">{{ \Illuminate\Support\Str::limit($f->extension(), 4, '') }}</div>
                            <div class="archivo-info"><strong>{{ $f->title }}</strong><small>{{ $f->original_name }} · {{ $f->humanSize() }}</small></div>
                            <button class="boton boton-chico boton-peligro" type="submit" form="borrar-archivo-{{ $f->id }}" data-confirmar="¿Eliminar este archivo?">Quitar</button>
                        </div>
                    @endforeach
                    <hr style="border:0;border-top:1px solid var(--color-borde);margin:16px 0">
                @endif
                <div class="campo" data-plantilla-archivo>
                    <input type="text" name="new_file_titles[]" placeholder="Nombre a mostrar (opcional)" style="margin-bottom:6px">
                    <input type="file" name="new_files[]">
                </div>
                <button class="boton boton-chico boton-secundario" type="button" data-agregar-archivo>+ Otro archivo</button>
                <small class="ayuda">PDF, Excel, Word, plantillas, etc. Máximo {{ config('cursos.max_archivo_mb') }} MB por archivo.</small>
            </div>

            <div class="tarjeta" style="margin-top:20px;position:sticky;top:90px">
                <button class="boton" type="submit" style="width:100%">{{ $module->exists ? 'Guardar cambios' : 'Crear módulo' }}</button>
                <small class="ayuda centro">Si subes un video grande, la carga puede tardar varios minutos. No cierres la página.</small>
            </div>
        </div>
    </div>
</form>

@if($module->exists)
    {{-- Formularios separados (no se pueden anidar dentro del formulario principal) --}}
    @foreach($module->files as $f)
        <form id="borrar-archivo-{{ $f->id }}" method="POST" action="{{ route('admin.modules.files.destroy', [$module, $f]) }}">@csrf @method('DELETE')</form>
    @endforeach

    <div class="tarjeta tarjeta-plana" style="margin-top:32px;border-color:var(--color-error-suave)">
        <h3>Zona de cuidado</h3>
        <div class="botones">
            @if($module->video_path)
                <form class="en-linea" method="POST" action="{{ route('admin.modules.video.destroy', $module) }}">@csrf @method('DELETE')<button class="boton boton-chico boton-peligro" type="submit" data-confirmar="¿Quitar el video subido?">Quitar video subido</button></form>
            @endif
            <form class="en-linea" method="POST" action="{{ route('admin.modules.destroy', $module) }}">@csrf @method('DELETE')<button class="boton boton-chico boton-peligro" type="submit" data-confirmar="¿Eliminar este módulo con su video, quiz y archivos? Esto no se puede deshacer.">Eliminar módulo</button></form>
        </div>
    </div>
@endif
@endsection

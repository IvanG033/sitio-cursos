@php($hay = session('status') || session('error') || $errors->any())
@if($hay)
    @if(!empty($envolver))<div class="contenedor" style="padding-top:24px">@endif
    @if(session('status'))
        <div class="aviso aviso-exito">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="aviso aviso-error">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="aviso aviso-error">
            Revisa lo siguiente:
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @if(!empty($envolver))</div>@endif
@endif

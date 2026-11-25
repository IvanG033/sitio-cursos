<div class="botones">
    @if($user->status === 'pending')
        <form class="en-linea" method="POST" action="{{ route('admin.users.approve', $user) }}">@csrf<button class="boton boton-chico boton-exito" type="submit">Dar acceso</button></form>
    @endif
    @if($user->status === 'blocked')
        <form class="en-linea" method="POST" action="{{ route('admin.users.unblock', $user) }}">@csrf<button class="boton boton-chico boton-exito" type="submit">Desbloquear</button></form>
    @else
        <form class="en-linea" method="POST" action="{{ route('admin.users.block', $user) }}">@csrf<button class="boton boton-chico boton-peligro" type="submit" data-confirmar="¿Bloquear a {{ $user->name }}? Ya no podrá entrar.">Bloquear</button></form>
    @endif
    <a class="boton boton-chico boton-secundario" href="{{ route('admin.messages.create', ['para' => $user->id]) }}">Mensaje</a>
</div>

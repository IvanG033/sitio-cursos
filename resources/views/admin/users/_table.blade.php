<div class="tabla-envoltura">
    <table class="tabla">
        <thead><tr><th>Nombre</th><th>Correo</th><th>Estado</th>@if($totalModules !== null)<th>Avance</th>@endif<th>Registro</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td><a href="{{ route('admin.users.show', $user) }}"><strong>{{ $user->name }}</strong></a></td>
                <td>{{ $user->email }}</td>
                <td>
                    <span @class(['etiqueta', 'etiqueta-alerta' => $user->status === 'pending', 'etiqueta-exito' => $user->status === 'approved', 'etiqueta-error' => $user->status === 'blocked'])>{{ $user->statusLabel() }}</span>
                </td>
                @if($totalModules !== null)
                    <td class="texto-suave">{{ $user->completed_count }} / {{ $totalModules }}</td>
                @endif
                <td class="texto-suave">{{ $user->created_at->format('d/m/Y') }}</td>
                <td class="acciones">
                    @include('admin.users._actions', ['user' => $user])
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

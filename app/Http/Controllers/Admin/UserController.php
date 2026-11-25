<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\User;
use App\Support\Correo;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $estado = $request->query('estado');
        $buscar = trim((string) $request->query('buscar'));

        $users = User::where('role', User::ROLE_STUDENT)
            ->when(in_array($estado, array_keys(User::STATUS_LABELS), true), fn ($q) => $q->where('status', $estado))
            ->when($buscar !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$buscar}%")->orWhere('email', 'like', "%{$buscar}%")))
            ->withCount(['progress as completed_count' => fn ($q) => $q->whereNotNull('completed_at')])
            ->orderByRaw("case status when 'pending' then 0 when 'approved' then 1 else 2 end")
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $totalModules = Module::published()->where('is_free', false)->count();

        return view('admin.users.index', compact('users', 'estado', 'buscar', 'totalModules'));
    }

    public function show(User $user)
    {
        $roadmap = Module::roadmapFor($user);
        $progress = $user->progress()->get()->keyBy('module_id');
        $messages = $user->messages()->take(10)->get();

        return view('admin.users.show', compact('user', 'roadmap', 'progress', 'messages'));
    }

    public function approve(User $user)
    {
        $this->guardStudent($user);
        $user->forceFill(['status' => User::STATUS_APPROVED, 'approved_at' => now(), 'blocked_at' => null])->save();

        Correo::enviar(
            $user->email,
            '¡Ya tienes acceso a los cursos!',
            "Hola {$user->name}, tu acceso fue aprobado. Ya puedes entrar y comenzar con el primer módulo.",
            'Entrar a mis cursos',
            route('dashboard'),
        );

        return back()->with('status', "{$user->name} ya tiene acceso a los cursos.");
    }

    public function block(User $user)
    {
        $this->guardStudent($user);
        $user->forceFill(['status' => User::STATUS_BLOCKED, 'blocked_at' => now()])->save();

        return back()->with('status', "{$user->name} fue bloqueado y ya no puede entrar.");
    }

    public function unblock(User $user)
    {
        $this->guardStudent($user);
        $user->forceFill(['status' => User::STATUS_APPROVED, 'blocked_at' => null, 'approved_at' => $user->approved_at ?? now()])->save();

        return back()->with('status', "{$user->name} fue desbloqueado y tiene acceso de nuevo.");
    }

    public function resetProgress(User $user)
    {
        $this->guardStudent($user);
        $user->progress()->delete();

        return back()->with('status', "Se reinició el avance de {$user->name}.");
    }

    public function destroy(User $user)
    {
        $this->guardStudent($user);
        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', "Se eliminó la cuenta de {$name}.");
    }

    private function guardStudent(User $user): void
    {
        abort_if($user->isAdmin(), 403, 'No se puede cambiar una cuenta de administrador desde aquí.');
    }
}

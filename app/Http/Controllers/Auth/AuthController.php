<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Correo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->onlyInput('email');
        }

        $user = Auth::user();
        if ($user->isBlocked()) {
            Auth::logout();

            return back()->withErrors(['email' => 'Tu cuenta está bloqueada. Si crees que es un error, contáctanos.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($user->isAdmin() ? route('admin.dashboard') : route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Ya existe una cuenta con este correo. ¿Quieres iniciar sesión?',
        ]);

        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        Correo::enviar(
            config('cursos.correo_avisos'),
            'Nuevo registro: '.$user->name,
            "{$user->name} ({$user->email}) se acaba de registrar y está esperando que le des acceso.",
            'Revisar en el administrador',
            route('admin.users.index', ['estado' => User::STATUS_PENDING]),
        );

        return redirect()->route('pending');
    }

    public function pending(Request $request)
    {
        if ($request->user()->isBlocked()) {
            return $this->logout($request);
        }
        if ($request->user()->isApproved() || $request->user()->isAdmin()) {
            return redirect()->route('dashboard');
        }

        return view('auth.pending');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}

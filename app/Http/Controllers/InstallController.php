<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules\Password;
use Throwable;

/**
 * Página /instalar: crea las tablas de la base de datos y la primera cuenta de
 * administrador desde el navegador, para hostings donde no hay SSH ni phpMyAdmin.
 * Solo funciona si INSTALL_KEY tiene un valor en el archivo .env.
 */
class InstallController extends Controller
{
    public function show()
    {
        abort_unless($this->enabled(), 404);

        return view('install', [
            'dbError' => $this->databaseError(),
            'installed' => $this->hasAdmin(),
        ]);
    }

    public function run(Request $request)
    {
        abort_unless($this->enabled(), 404);

        $installed = $this->hasAdmin();
        $rules = ['clave' => ['required', 'string']];
        if (! $installed) {
            $rules += [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:180'],
                'password' => ['required', 'confirmed', Password::min(8)],
            ];
        }
        $data = $request->validate($rules);

        if (! hash_equals((string) config('cursos.clave_instalacion'), (string) $data['clave'])) {
            return back()->withInput($request->except('clave', 'password', 'password_confirmation'))
                ->withErrors(['clave' => 'La clave de instalación no es correcta. Cópiala de la línea INSTALL_KEY del archivo .env.']);
        }

        if ($error = $this->databaseError()) {
            return back()->withErrors(['clave' => $error]);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['clave' => 'No se pudieron crear las tablas: '.$e->getMessage()]);
        }

        if ($installed) {
            return redirect()->route('install')->with('status', 'Base de datos actualizada. No había nada más que hacer.');
        }

        $user = User::firstOrNew(['email' => $data['email']]);
        $user->name = $data['name'];
        $user->password = $data['password'];
        $user->forceFill(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_APPROVED, 'approved_at' => now()])->save();

        return redirect()->route('login')->with('status', '¡Instalación completa! Ya puedes entrar con tu cuenta de administrador.');
    }

    private function enabled(): bool
    {
        return filled(config('cursos.clave_instalacion'));
    }

    private function databaseError(): ?string
    {
        try {
            DB::connection()->getPdo();

            return null;
        } catch (Throwable $e) {
            return 'No se pudo conectar a la base de datos. Revisa en el archivo .env las líneas DB_HOST, DB_DATABASE, DB_USERNAME y DB_PASSWORD. (Detalle: '.$e->getMessage().')';
        }
    }

    private function hasAdmin(): bool
    {
        try {
            return Schema::hasTable('users') && User::where('role', User::ROLE_ADMIN)->exists();
        } catch (Throwable) {
            return false;
        }
    }
}

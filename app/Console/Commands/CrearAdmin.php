<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CrearAdmin extends Command
{
    protected $signature = 'cursos:crear-admin {email} {--name=Administrador} {--password=}';

    protected $description = 'Crea (o convierte) una cuenta de administrador';

    public function handle(): int
    {
        $password = $this->option('password') ?: $this->secret('Contraseña para el administrador (mínimo 8 caracteres)');
        if (strlen((string) $password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $this->argument('email')]);
        $user->name = $user->name ?: $this->option('name');
        $user->password = $password;
        $user->forceFill([
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_APPROVED,
            'approved_at' => now(),
        ])->save();

        $this->info("Listo: {$user->email} es administrador.");

        return self::SUCCESS;
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstalacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_clave_la_pagina_no_existe(): void
    {
        config(['cursos.clave_instalacion' => null]);
        $this->get('/instalar')->assertNotFound();
    }

    public function test_clave_incorrecta_no_instala(): void
    {
        config(['cursos.clave_instalacion' => 'secreta-123']);
        $this->get('/instalar')->assertOk()->assertSee('Instalar el sitio');
        $this->post('/instalar', [
            'clave' => 'otra', 'name' => 'Iván', 'email' => 'ivan@ejemplo.com',
            'password' => 'segura123', 'password_confirmation' => 'segura123',
        ])->assertSessionHasErrors('clave');
        $this->assertSame(0, User::count());
    }

    public function test_instala_y_crea_administrador_una_sola_vez(): void
    {
        config(['cursos.clave_instalacion' => 'secreta-123']);
        $this->post('/instalar', [
            'clave' => 'secreta-123', 'name' => 'Iván', 'email' => 'ivan@ejemplo.com',
            'password' => 'segura123', 'password_confirmation' => 'segura123',
        ])->assertRedirect(route('login'));

        $admin = User::firstWhere('email', 'ivan@ejemplo.com');
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isApproved());

        // Ya instalado: solo pide la clave y no crea otra cuenta
        $this->get('/instalar')->assertOk()->assertSee('ya está instalado');
        $this->post('/instalar', [
            'clave' => 'secreta-123', 'name' => 'Intruso', 'email' => 'otro@ejemplo.com',
            'password' => 'segura123', 'password_confirmation' => 'segura123',
        ])->assertRedirect(route('install'));
        $this->assertSame(1, User::count());
    }
}

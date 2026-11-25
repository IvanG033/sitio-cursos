<?php

namespace Tests\Feature;

use App\Mail\AvisoMail;
use App\Models\Message;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CursosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $u = User::factory()->create();
        $u->forceFill(['role' => 'admin', 'status' => 'approved'])->save();

        return $u;
    }

    private function student(string $status = 'approved'): User
    {
        $u = User::factory()->create();
        $u->forceFill(['status' => $status])->save();

        return $u;
    }

    private function moduleWithQuiz(int $position, bool $free = false): Module
    {
        $m = Module::create(['title' => "Módulo $position", 'position' => $position, 'is_published' => true, 'is_free' => $free]);
        $q = $m->questions()->create(['text' => '¿2+2?', 'position' => 1]);
        $q->options()->create(['text' => '3', 'is_correct' => false, 'position' => 1]);
        $q->options()->create(['text' => '4', 'is_correct' => true, 'position' => 2]);

        return $m->load('questions.options');
    }

    public function test_registro_queda_pendiente_y_avisa_al_admin(): void
    {
        Mail::fake();
        config(['cursos.correo_avisos' => 'avisos@ejemplo.com']);

        $this->post('/registro', [
            'name' => 'Ana', 'email' => 'ana@ejemplo.com',
            'password' => 'secreto123', 'password_confirmation' => 'secreto123',
        ])->assertRedirect(route('pending'));

        $this->assertSame('pending', User::firstWhere('email', 'ana@ejemplo.com')->status);
        Mail::assertSent(AvisoMail::class, fn ($m) => $m->hasTo('avisos@ejemplo.com'));
        $this->get('/mis-cursos')->assertRedirect(route('pending'));
    }

    public function test_admin_aprueba_y_bloquea(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $user = $this->student('pending');

        $this->actingAs($admin)->post(route('admin.users.approve', $user))->assertRedirect();
        $this->assertTrue($user->fresh()->isApproved());
        Mail::assertSent(AvisoMail::class, fn ($m) => $m->hasTo($user->email));

        $this->actingAs($user->fresh())->get("/mis-cursos")->assertOk();

        $this->actingAs($admin)->post(route('admin.users.block', $user));
        $this->assertTrue($user->fresh()->isBlocked());
        $this->actingAs($user->fresh())->get('/mis-cursos')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_usuario_bloqueado_no_puede_entrar(): void
    {
        $user = $this->student('blocked');
        $this->post('/entrar', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_alumno_no_entra_al_admin(): void
    {
        $this->actingAs($this->student())->get('/admin')->assertForbidden();
    }

    public function test_modulos_se_desbloquean_en_orden_y_libres_siempre_abiertos(): void
    {
        $user = $this->student();
        $m1 = $this->moduleWithQuiz(1);
        $m2 = $this->moduleWithQuiz(2);
        $libre = $this->moduleWithQuiz(3, free: true);
        $m3 = $this->moduleWithQuiz(4);

        $this->actingAs($user)->get(route('modules.show', $m1))->assertOk();
        $this->actingAs($user)->get(route('modules.show', $m2))->assertRedirect(route('dashboard'));
        $this->actingAs($user)->get(route('modules.show', $libre))->assertOk();

        // Respuesta incorrecta: sigue bloqueado
        $wrong = $m1->questions[0]->options->firstWhere('is_correct', false);
        $this->actingAs($user)->post(route('modules.quiz', $m1), ['answers' => [$m1->questions[0]->id => $wrong->id]]);
        $this->actingAs($user)->get(route('modules.show', $m2))->assertRedirect(route('dashboard'));

        // Respuesta correcta: se abre el 2, pero no el 3 (el libre no cuenta)
        $right = $m1->questions[0]->options->firstWhere('is_correct', true);
        $this->actingAs($user)->post(route('modules.quiz', $m1), ['answers' => [$m1->questions[0]->id => $right->id]]);
        $this->actingAs($user)->get(route('modules.show', $m2))->assertOk();
        $this->actingAs($user)->get(route('modules.show', $m3))->assertRedirect(route('dashboard'));

        $this->actingAs($user)->get('/mis-cursos')->assertOk()->assertSee('Módulo 1')->assertSee('Completado');
    }

    public function test_modulo_sin_quiz_se_completa_con_boton(): void
    {
        $user = $this->student();
        $m1 = Module::create(['title' => 'Intro', 'position' => 1, 'is_published' => true]);
        $m2 = Module::create(['title' => 'Dos', 'position' => 2, 'is_published' => true]);

        $this->actingAs($user)->post(route('modules.complete', $m1))->assertRedirect();
        $this->actingAs($user)->get(route('modules.show', $m2))->assertOk();
    }

    public function test_borradores_no_se_ven(): void
    {
        $m = Module::create(['title' => 'Borrador', 'position' => 1, 'is_published' => false]);
        $this->actingAs($this->student())->get(route('modules.show', $m))->assertNotFound();
        $this->actingAs($this->admin())->get(route('modules.show', $m))->assertOk();
    }

    public function test_admin_crea_modulo_con_video_quiz_y_archivos(): void
    {
        Storage::fake('local');
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.modules.store'), [
            'title' => 'SEO básico',
            'summary' => 'Aparece en Google',
            'is_published' => '1',
            'is_free' => '0',
            'video' => UploadedFile::fake()->create('clase.mp4', 1000, 'video/mp4'),
            'new_file_titles' => ['Plantilla'],
            'new_files' => [UploadedFile::fake()->create('plantilla.pdf', 100, 'application/pdf')],
            'questions' => [
                ['text' => '¿Qué es SEO?', 'options' => ['Posicionamiento', 'Anuncios', '', ''], 'correct' => '0'],
                ['text' => '¿Qué es una keyword?', 'options' => ['Palabra clave', 'Contraseña', 'Un color', ''], 'correct' => '0'],
                ['text' => '', 'options' => ['', '', '', '']],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $m = Module::firstWhere('title', 'SEO básico');
        $this->assertNotNull($m->video_path);
        Storage::disk('local')->assertExists($m->video_path);
        $this->assertCount(2, $m->questions);
        $this->assertTrue($m->questions[0]->options[0]->is_correct);
        $this->assertCount(1, $m->files);
        $this->assertSame('Plantilla', $m->files[0]->title);

        // El alumno puede ver el video y descargar el archivo
        $user = $this->student();
        $this->actingAs($user)->get(route('modules.download', [$m, $m->files[0]]))->assertOk();
        $this->actingAs($user)->get(route('modules.video', $m))->assertOk();

        // Formulario de edición carga bien
        $this->actingAs($admin)->get(route('admin.modules.edit', $m))->assertOk()->assertSee('¿Qué es SEO?');
    }

    public function test_quiz_sin_respuesta_correcta_marcada_da_error(): void
    {
        $this->actingAs($this->admin())->post(route('admin.modules.store'), [
            'title' => 'X',
            'questions' => [['text' => 'Pregunta', 'options' => ['a', 'b', '', '']]],
        ])->assertSessionHasErrors('questions.0.text');
    }

    public function test_admin_envia_mensaje_a_todos_con_correo(): void
    {
        Mail::fake();
        $admin = $this->admin();
        $a = $this->student();
        $b = $this->student();
        $this->student('blocked');

        $this->actingAs($admin)->post(route('admin.messages.store'), [
            'to' => 'approved', 'subject' => 'Nuevo módulo', 'body' => 'Ya está disponible', 'send_email' => '1',
        ])->assertRedirect(route('admin.messages.index'));

        $this->assertSame(2, Message::count());
        Mail::assertSent(AvisoMail::class, 2);

        $msg = Message::firstWhere('user_id', $a->id);
        $this->actingAs($a)->get(route('inbox.index'))->assertSee('Nuevo módulo');
        $this->actingAs($a)->get(route('inbox.show', $msg))->assertOk();
        $this->assertNotNull($msg->fresh()->read_at);
        $this->actingAs($b)->get(route('inbox.show', $msg))->assertNotFound();
    }

    public function test_paginas_publicas_y_admin_cargan(): void
    {
        $this->get('/')->assertOk();
        $this->get('/entrar')->assertOk();
        $this->get('/registro')->assertOk();
        $this->get('/olvide-mi-contrasena')->assertOk();

        $admin = $this->admin();
        $this->moduleWithQuiz(1);
        $this->student('pending');
        foreach (['admin.dashboard', 'admin.users.index', 'admin.modules.index', 'admin.modules.create', 'admin.messages.index', 'admin.messages.create'] as $r) {
            $this->actingAs($admin)->get(route($r))->assertOk();
        }
    }

    public function test_reordenar_modulos(): void
    {
        $admin = $this->admin();
        $a = Module::create(['title' => 'A', 'position' => 1]);
        $b = Module::create(['title' => 'B', 'position' => 2]);
        $this->actingAs($admin)->post(route('admin.modules.move', $b), ['direction' => 'up']);
        $this->assertSame(['B', 'A'], Module::ordered()->pluck('title')->all());
    }
}

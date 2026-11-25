<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Seeder;

/** Datos de ejemplo para probar el sitio. No usar en producción. */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@ejemplo.com']);
        $admin->fill(['name' => 'Administrador', 'password' => 'admin12345'])
            ->forceFill(['role' => 'admin', 'status' => 'approved', 'approved_at' => now()])->save();

        $alumno = User::firstOrNew(['email' => 'alumno@ejemplo.com']);
        $alumno->fill(['name' => 'Ana Alumna', 'password' => 'alumno12345'])
            ->forceFill(['status' => 'approved', 'approved_at' => now()])->save();

        User::firstOrCreate(['email' => 'nuevo@ejemplo.com'], ['name' => 'Carlos Nuevo', 'password' => 'nuevo12345']);

        $temas = [
            ['Fundamentos del marketing digital', 'Qué es, cómo funciona el embudo y por dónde empezar.', false],
            ['Conoce a tu cliente ideal', 'Construye tu buyer persona y su recorrido de compra.', false],
            ['Redes sociales con estrategia', 'Elige canales, define pilares de contenido y calendario.', false],
            ['Publicidad pagada en Meta', 'Estructura de campañas, públicos y presupuesto.', false],
            ['Glosario de marketing digital', 'Términos clave para consultar cuando los necesites.', true],
            ['Herramientas gratuitas recomendadas', 'Canva, Metricool, Google Analytics y más.', true],
        ];

        foreach ($temas as $i => [$titulo, $resumen, $libre]) {
            $m = Module::firstOrCreate(['title' => $titulo], [
                'summary' => $resumen,
                'content' => "Puntos clave de la lección:\n• Idea principal 1\n• Idea principal 2\n• Idea principal 3",
                'position' => $i + 1,
                'is_free' => $libre,
                'is_published' => true,
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ]);
            if ($m->questions()->exists() || $libre) {
                continue;
            }
            foreach ([
                ['¿Cuál es el primer paso de una estrategia digital?', ['Publicar todos los días', 'Definir objetivos y público', 'Comprar anuncios'], 1],
                ['¿Qué mide la tasa de conversión?', ['Seguidores nuevos', 'Porcentaje que realiza la acción deseada', 'Alcance de una publicación'], 1],
            ] as $qi => [$texto, $opciones, $correcta]) {
                $q = $m->questions()->create(['text' => $texto, 'position' => $qi + 1]);
                foreach ($opciones as $oi => $op) {
                    $q->options()->create(['text' => $op, 'is_correct' => $oi === $correcta, 'position' => $oi + 1]);
                }
            }
        }
    }
}

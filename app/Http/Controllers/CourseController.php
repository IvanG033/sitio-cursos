<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\ModuleFile;
use App\Models\ModuleProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $roadmap = Module::roadmapFor($user);
        $main = $roadmap->where('module.is_free', false)->values();
        $free = $roadmap->where('module.is_free', true)->values();

        $done = $main->where('completed', true)->count();
        $percent = $main->count() ? (int) round($done / $main->count() * 100) : 0;
        $next = $main->first(fn ($e) => $e['unlocked'] && ! $e['completed']);

        return view('course.index', compact('main', 'free', 'done', 'percent', 'next'));
    }

    public function show(Request $request, Module $module)
    {
        $user = $request->user();
        $this->authorizeModule($request, $module);

        $module->load(['questions.options', 'files']);
        $progress = ModuleProgress::firstWhere(['user_id' => $user->id, 'module_id' => $module->id]);

        $roadmap = Module::roadmapFor($user);
        $index = $roadmap->search(fn ($e) => $e['module']->id === $module->id);
        $nextEntry = $module->is_free ? null : $roadmap->slice($index + 1)->first(fn ($e) => ! $e['module']->is_free);

        return view('course.show', [
            'module' => $module,
            'progress' => $progress,
            'nextEntry' => $nextEntry,
            'roadmap' => $roadmap,
            'result' => session('quiz_result'),
        ]);
    }

    public function submitQuiz(Request $request, Module $module)
    {
        $user = $request->user();
        $this->authorizeModule($request, $module);
        $module->load('questions.options');
        abort_if($module->questions->isEmpty(), 404);

        $rules = [];
        foreach ($module->questions as $q) {
            $rules['answers.'.$q->id] = ['required', 'integer'];
        }
        $request->validate($rules, ['answers.*.required' => 'Responde todas las preguntas antes de enviar.']);

        $answers = $request->input('answers', []);
        $correct = 0;
        $detail = [];
        foreach ($module->questions as $q) {
            $chosen = (int) ($answers[$q->id] ?? 0);
            $ok = $q->options->firstWhere('id', $chosen)?->is_correct === true;
            $correct += $ok ? 1 : 0;
            $detail[$q->id] = ['chosen' => $chosen, 'ok' => $ok];
        }

        $score = (int) round($correct / $module->questions->count() * 100);
        $passed = $score >= config('cursos.porcentaje_aprobacion');

        $progress = ModuleProgress::firstOrNew(['user_id' => $user->id, 'module_id' => $module->id]);
        $progress->attempts = ($progress->attempts ?? 0) + 1;
        $progress->best_score = max((int) $progress->best_score, $score);
        if ($passed && ! $progress->completed_at) {
            $progress->completed_at = now();
        }
        $progress->save();

        return redirect()->route('modules.show', $module)->with('quiz_result', [
            'score' => $score,
            'correct' => $correct,
            'total' => $module->questions->count(),
            'passed' => $passed,
            'detail' => $detail,
        ])->withFragment('quiz');
    }

    /** Para módulos sin quiz: el alumno marca la lección como vista. */
    public function complete(Request $request, Module $module)
    {
        $this->authorizeModule($request, $module);
        abort_if($module->hasQuiz(), 422, 'Este módulo se completa aprobando el quiz.');

        $progress = ModuleProgress::firstOrNew(['user_id' => $request->user()->id, 'module_id' => $module->id]);
        $progress->best_score = 100;
        $progress->completed_at ??= now();
        $progress->save();

        return redirect()->route('modules.show', $module)->with('status', '¡Módulo completado!');
    }

    public function video(Request $request, Module $module)
    {
        $this->authorizeModule($request, $module);
        abort_unless($module->video_path && Storage::disk('local')->exists($module->video_path), 404);

        // BinaryFileResponse permite adelantar/retroceder el video (peticiones Range).
        return response()->file(Storage::disk('local')->path($module->video_path), [
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function download(Request $request, Module $module, ModuleFile $file)
    {
        abort_unless($file->module_id === $module->id, 404);
        $this->authorizeModule($request, $module);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return Storage::disk('local')->download($file->path, $file->original_name);
    }

    private function authorizeModule(Request $request, Module $module): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return; // el administrador puede ver todo, incluso borradores
        }
        abort_unless($module->is_published, 404);
        if (! $module->isUnlockedFor($user)) {
            abort(redirect()->route('dashboard')->with('error', 'Este módulo todavía está bloqueado. Completa primero el módulo anterior.'));
        }
    }
}

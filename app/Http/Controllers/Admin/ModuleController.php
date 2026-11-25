<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = Module::ordered()->withCount(['questions', 'files'])->get();

        return view('admin.modules.index', compact('modules'));
    }

    public function create()
    {
        return view('admin.modules.form', ['module' => new Module(['is_published' => true])]);
    }

    public function store(Request $request)
    {
        $module = new Module($this->validated($request));
        $module->position = (int) Module::max('position') + 1;
        $module->save();
        $this->handleVideo($request, $module);
        $this->saveQuiz($request, $module);
        $this->handleFiles($request, $module);

        return redirect()->route('admin.modules.edit', $module)->with('status', 'Módulo creado. Puedes seguir editándolo aquí.');
    }

    public function edit(Module $module)
    {
        $module->load(['questions.options', 'files']);

        return view('admin.modules.form', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        $module->fill($this->validated($request))->save();
        $this->handleVideo($request, $module);
        $this->saveQuiz($request, $module);
        $this->handleFiles($request, $module);

        return redirect()->route('admin.modules.edit', $module)->with('status', 'Cambios guardados.');
    }

    public function destroy(Module $module)
    {
        if ($module->video_path) {
            Storage::disk('local')->delete($module->video_path);
        }
        foreach ($module->files as $file) {
            Storage::disk('local')->delete($file->path);
        }
        $module->delete();

        return redirect()->route('admin.modules.index')->with('status', 'Módulo eliminado.');
    }

    public function move(Request $request, Module $module)
    {
        $direction = $request->input('direction') === 'up' ? 'up' : 'down';
        $modules = Module::ordered()->get()->values();
        $i = $modules->search(fn ($m) => $m->id === $module->id);
        $j = $direction === 'up' ? $i - 1 : $i + 1;

        if ($j >= 0 && $j < $modules->count()) {
            $items = $modules->all();
            [$items[$i], $items[$j]] = [$items[$j], $items[$i]];
            DB::transaction(function () use ($items) {
                foreach (array_values($items) as $pos => $m) {
                    $m->update(['position' => $pos + 1]);
                }
            });
        }

        return back();
    }

    public function removeVideo(Module $module)
    {
        if ($module->video_path) {
            Storage::disk('local')->delete($module->video_path);
        }
        $module->forceFill(['video_path' => null, 'video_original_name' => null])->save();

        return back()->with('status', 'Video eliminado.');
    }

    public function destroyFile(Module $module, ModuleFile $file)
    {
        abort_unless($file->module_id === $module->id, 404);
        Storage::disk('local')->delete($file->path);
        $file->delete();

        return back()->with('status', 'Archivo eliminado.');
    }

    private function validated(Request $request): array
    {
        $videoMax = config('cursos.max_video_mb') * 1024;
        $fileMax = config('cursos.max_archivo_mb') * 1024;

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:500'],
            'video' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/quicktime,video/ogg', 'max:'.$videoMax],
            'new_files' => ['nullable', 'array'],
            'new_files.*' => ['file', 'max:'.$fileMax],
            'new_file_titles' => ['nullable', 'array'],
            'questions' => ['nullable', 'array', 'max:5'],
            'questions.*.text' => ['nullable', 'string', 'max:500'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*' => ['nullable', 'string', 'max:300'],
            'questions.*.correct' => ['nullable', 'integer'],
        ], [
            'video.mimetypes' => 'El video debe ser MP4, WebM, MOV u OGG.',
            'video.max' => 'El video es demasiado grande (máximo '.config('cursos.max_video_mb').' MB).',
            'video.uploaded' => 'El servidor no aceptó el video. Probablemente es más grande que el límite del hosting (ver docs/DREAMHOST.md).',
            'new_files.*.max' => 'Cada archivo puede pesar hasta '.config('cursos.max_archivo_mb').' MB.',
            'new_files.*.uploaded' => 'El servidor no aceptó uno de los archivos. Probablemente supera el límite del hosting.',
        ]);

        foreach ($request->input('questions', []) as $n => $q) {
            if (trim((string) ($q['text'] ?? '')) === '') {
                continue;
            }
            $options = array_filter($q['options'] ?? [], fn ($o) => trim((string) $o) !== '');
            if (count($options) < 2) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "questions.$n.text" => 'La pregunta '.($n + 1).' necesita al menos 2 opciones.',
                ]);
            }
            if (! isset($q['correct']) || ! isset($options[(int) $q['correct']])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "questions.$n.text" => 'Marca cuál es la respuesta correcta de la pregunta '.($n + 1).'.',
                ]);
            }
        }

        return [
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'is_free' => $request->boolean('is_free'),
            'is_published' => $request->boolean('is_published'),
        ];
    }

    private function handleVideo(Request $request, Module $module): void
    {
        if (! $request->hasFile('video')) {
            return;
        }
        if ($module->video_path) {
            Storage::disk('local')->delete($module->video_path);
        }
        $file = $request->file('video');
        $module->forceFill([
            'video_path' => $file->store('videos', 'local'),
            'video_original_name' => $file->getClientOriginalName(),
        ])->save();
    }

    private function handleFiles(Request $request, Module $module): void
    {
        $titles = $request->input('new_file_titles', []);
        foreach ($request->file('new_files', []) as $i => $file) {
            $original = $file->getClientOriginalName();
            $module->files()->create([
                'title' => trim((string) ($titles[$i] ?? '')) ?: pathinfo($original, PATHINFO_FILENAME),
                'path' => $file->store('archivos', 'local'),
                'original_name' => $original,
                'size' => $file->getSize(),
            ]);
        }
    }

    /** Reemplaza el quiz del módulo con lo que venga en el formulario. */
    private function saveQuiz(Request $request, Module $module): void
    {
        if (! $request->has('questions')) {
            return;
        }

        DB::transaction(function () use ($request, $module) {
            $module->questions()->delete();
            $position = 0;
            foreach ($request->input('questions', []) as $q) {
                $text = trim((string) ($q['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $question = $module->questions()->create(['text' => $text, 'position' => ++$position]);
                $optPos = 0;
                foreach ($q['options'] ?? [] as $idx => $optText) {
                    $optText = trim((string) $optText);
                    if ($optText === '') {
                        continue;
                    }
                    $question->options()->create([
                        'text' => $optText,
                        'is_correct' => (string) $idx === (string) ($q['correct'] ?? ''),
                        'position' => ++$optPos,
                    ]);
                }
            }
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Module extends Model
{
    protected $fillable = [
        'title', 'summary', 'content', 'position', 'is_free', 'is_published', 'video_url',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ModuleFile::class)->orderBy('id');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function hasQuiz(): bool
    {
        return $this->questions()->exists();
    }

    public function hasVideo(): bool
    {
        return $this->video_path || $this->video_url;
    }

    /**
     * Convierte un enlace de YouTube o Vimeo en una URL para incrustar.
     */
    public function embedUrl(): ?string
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1].'?rel=0';
        }
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)(?:/(\w+))?~', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1].(isset($m[2]) ? '?h='.$m[2] : '');
        }

        return null;
    }

    /**
     * Calcula, para un alumno, qué módulos publicados están desbloqueados.
     * Regla: los módulos "libres" siempre están abiertos. Los módulos de la
     * ruta principal se abren en orden: cada uno requiere haber completado
     * el módulo anterior de la ruta.
     *
     * @return Collection<int, array{module: Module, unlocked: bool, completed: bool}>
     */
    public static function roadmapFor(User $user): Collection
    {
        $modules = static::published()->ordered()->withCount('questions')->get();
        $completed = $user->progress()->whereNotNull('completed_at')->pluck('module_id')->flip();

        $previousDone = true;

        return $modules->map(function (Module $module) use (&$previousDone, $completed) {
            $isDone = $completed->has($module->id);
            if ($module->is_free) {
                $unlocked = true;
            } else {
                $unlocked = $previousDone;
                $previousDone = $isDone;
            }

            return ['module' => $module, 'unlocked' => $unlocked, 'completed' => $isDone];
        });
    }

    public function isUnlockedFor(User $user): bool
    {
        if (! $this->is_published) {
            return false;
        }
        if ($this->is_free) {
            return true;
        }
        $entry = static::roadmapFor($user)->first(fn ($e) => $e['module']->id === $this->id);

        return (bool) ($entry['unlocked'] ?? false);
    }
}

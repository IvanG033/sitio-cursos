<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleFile extends Model
{
    protected $fillable = ['module_id', 'title', 'path', 'original_name', 'size'];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function humanSize(): string
    {
        $size = (float) $this->size;
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($size < 1024 || $unit === 'GB') {
                return round($size, $unit === 'B' ? 0 : 1).' '.$unit;
            }
            $size /= 1024;
        }

        return $this->size.' B';
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION) ?: 'archivo');
    }
}

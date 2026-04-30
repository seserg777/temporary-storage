<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable([
    'original_name',
    'stored_name',
    'mime_type',
    'size',
    'disk',
    'path',
    'expires_at',
])]
class UploadedFile extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'size'       => 'integer',
        ];
    }

    /**
     * Scope to files that have passed their retention window.
     *
     * @param  Builder<UploadedFile>  $query
     * @return Builder<UploadedFile>
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }
}
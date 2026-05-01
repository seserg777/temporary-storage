<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $expires_at
 */
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
            'size' => 'integer',
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

    /** @return Attribute<string, never> */
    protected function formattedSize(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                $bytes = $this->size;

                if ($bytes < 1024) {
                    return $bytes.' B';
                }

                if ($bytes < 1_048_576) {
                    return number_format($bytes / 1024, 2).' KB';
                }

                return number_format($bytes / 1_048_576, 2).' MB';
            },
        );
    }
}

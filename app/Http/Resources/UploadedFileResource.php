<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UploadedFile
 */
class UploadedFileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'size' => $this->size,
            'expires_at' => $this->expires_at->toIso8601String(),
        ];
    }
}
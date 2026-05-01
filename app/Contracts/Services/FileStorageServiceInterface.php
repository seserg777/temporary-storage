<?php

declare(strict_types=1);

namespace App\Contracts\Services;

use App\Models\UploadedFile;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

interface FileStorageServiceInterface
{
    /**
     * Persist the uploaded file to disk and record metadata in the DB.
     */
    public function store(HttpUploadedFile $file): UploadedFile;

    /**
     * Delete both the DB record and the physical file on disk.
     */
    public function delete(UploadedFile $file): void;

    /**
     * Return a paginated listing of all uploaded files, newest first.
     *
     * @return LengthAwarePaginator<UploadedFile>
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator;

    /**
     * Find a single record or throw ModelNotFoundException.
     */
    public function findOrFail(int $id): UploadedFile;

    /**
     * Delete all expired records and their physical files.
     * Returns the count of deleted records.
     */
    public function deleteExpired(): int;
}

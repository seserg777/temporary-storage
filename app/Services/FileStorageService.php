<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Jobs\SendCleanupSummaryNotificationJob;
use App\Jobs\SendFileDeletedNotificationJob;
use App\Models\UploadedFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class FileStorageService implements FileStorageServiceInterface
{
    private const string DISK = 'public';

    private const string DIRECTORY = 'uploads';

    private const int RETENTION_HOURS = 24;

    public function store(HttpUploadedFile $file): UploadedFile
    {
        $storedPath = null;

        try {
            $storedPath = Storage::disk(self::DISK)->putFile(self::DIRECTORY, $file);

            if ($storedPath === false) {
                throw new \RuntimeException('Failed to store file on disk.');
            }

            return DB::transaction(function () use ($file, $storedPath): UploadedFile {
                return UploadedFile::create([
                    'original_name' => $file->getClientOriginalName(),
                    'stored_name' => basename($storedPath),
                    'mime_type' => (string) $file->getMimeType(),
                    'size' => $file->getSize(),
                    'disk' => self::DISK,
                    'path' => $storedPath,
                    'expires_at' => Carbon::now()->addHours(self::RETENTION_HOURS),
                ]);
            });
        } catch (Throwable $e) {
            if ($storedPath !== null && $storedPath !== false) {
                Storage::disk(self::DISK)->delete($storedPath);
            }

            throw $e;
        }
    }

    public function delete(UploadedFile $file): void
    {
        $originalName = $file->original_name;
        $deletedAt = now()->toDateTimeString();

        DB::transaction(function () use ($file): void {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        });

        try {
            SendFileDeletedNotificationJob::dispatch($originalName, $deletedAt);
        } catch (Throwable $e) {
            Log::error('File-deleted notification could not be queued.', [
                'file' => $originalName,
                'deleted_at' => $deletedAt,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return LengthAwarePaginator<int, UploadedFile>
     */
    public function paginate(int $perPage = 20): LengthAwarePaginator
    {
        return UploadedFile::query()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): UploadedFile
    {
        /** @var UploadedFile */
        return UploadedFile::findOrFail($id);
    }

    public function deleteExpired(): int
    {
        /** @var Collection<int, UploadedFile> $expired */
        $expired = UploadedFile::expired()->get();

        if ($expired->isEmpty()) {
            return 0;
        }

        /** @var array<int, array{original_name: string, expires_at: string}> $deletedFiles */
        $deletedFiles = [];

        foreach ($expired as $file) {
            $deletedFiles[] = [
                'original_name' => $file->original_name,
                'expires_at' => $file->expires_at->toDateTimeString(),
            ];
            $this->deleteFileAndRecord($file);
        }

        try {
            SendCleanupSummaryNotificationJob::dispatch($deletedFiles);
        } catch (Throwable $e) {
            Log::error('Cleanup summary notification could not be queued.', [
                'file_count' => count($deletedFiles),
                'error' => $e->getMessage(),
            ]);
        }

        return count($deletedFiles);
    }

    private function deleteFileAndRecord(UploadedFile $file): void
    {
        DB::transaction(function () use ($file): void {
            Storage::disk($file->disk)->delete($file->path);
            $file->delete();
        });
    }
}

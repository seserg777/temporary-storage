<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\Services\FileStorageServiceInterface;
use Illuminate\Console\Command;

class FilesCleanupCommand extends Command
{
    protected $signature = 'files:cleanup';

    protected $description = 'Delete uploaded files that have passed their expiry date.';

    public function handle(FileStorageServiceInterface $fileStorageService): int
    {
        $count = $fileStorageService->deleteExpired();

        $this->info("Deleted {$count} expired file(s).");

        return self::SUCCESS;
    }
}

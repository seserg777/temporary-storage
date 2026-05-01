<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\FilesCleanupSummaryMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendCleanupSummaryNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /**
     * @param  array<int, array{original_name: string, expires_at: string}>  $deletedFiles
     */
    public function __construct(
        public readonly array $deletedFiles,
    ) {}

    public function handle(): void
    {
        $recipient = (string) config('app.notify_email');

        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new FilesCleanupSummaryMail(
                deletedFiles: $this->deletedFiles,
            ));
        } catch (Throwable $e) {
            Log::error('Failed to send cleanup summary notification email.', [
                'file_count' => count($this->deletedFiles),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('SendCleanupSummaryNotificationJob permanently failed after all retries.', [
            'file_count' => count($this->deletedFiles),
            'error' => $e->getMessage(),
        ]);
    }
}

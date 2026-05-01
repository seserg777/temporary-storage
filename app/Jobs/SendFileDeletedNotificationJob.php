<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\FileDeletedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendFileDeletedNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly string $originalName,
        public readonly string $deletedAt,
    ) {}

    public function handle(): void
    {
        $recipient = (string) config('app.notify_email');

        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new FileDeletedMail(
                originalName: $this->originalName,
                deletedAt: $this->deletedAt,
            ));
        } catch (Throwable $e) {
            Log::error('Failed to send file-deleted notification email.', [
                'file' => $this->originalName,
                'deleted_at' => $this->deletedAt,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $e): void
    {
        Log::error('SendFileDeletedNotificationJob permanently failed after all retries.', [
            'file' => $this->originalName,
            'deleted_at' => $this->deletedAt,
            'error' => $e->getMessage(),
        ]);
    }
}

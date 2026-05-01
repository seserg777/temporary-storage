<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FilesCleanupSummaryMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array<int, array{original_name: string, expires_at: string}>  $deletedFiles
     */
    public function __construct(
        public readonly array $deletedFiles,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Files Cleanup Summary — '.count($this->deletedFiles).' file(s) deleted');
    }

    public function content(): Content
    {
        return new Content(view: 'mail.files-cleanup-summary');
    }
}

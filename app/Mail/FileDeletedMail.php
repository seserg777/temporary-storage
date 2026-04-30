<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FileDeletedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly string $originalName,
        public readonly string $deletedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'File Deleted — '.$this->originalName);
    }

    public function content(): Content
    {
        return new Content(view: 'mail.file-deleted');
    }
}

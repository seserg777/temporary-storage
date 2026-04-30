<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendFileDeletedNotificationJob;
use App\Mail\FileDeletedMail;
use App\Models\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_can_delete_existing_file(): void
    {
        $fakePath = 'uploads/test-file.pdf';
        Storage::disk('public')->put($fakePath, 'fake content');

        $file = UploadedFile::create([
            'original_name' => 'test.pdf',
            'stored_name'   => 'test-file.pdf',
            'mime_type'     => 'application/pdf',
            'size'          => 12,
            'disk'          => 'public',
            'path'          => $fakePath,
            'expires_at'    => Carbon::now()->addDays(7),
        ]);

        $response = $this->delete(route('files.destroy', $file));

        $response->assertRedirect(route('files.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('uploaded_files', ['id' => $file->id]);
        Storage::disk('public')->assertMissing($fakePath);
    }

    public function test_delete_returns_404_for_nonexistent_file(): void
    {
        $response = $this->delete(route('files.destroy', 9999));

        $response->assertNotFound();
    }

    public function test_deleting_a_file_dispatches_notification_job(): void
    {
        Queue::fake();

        $fakePath = 'uploads/test-file.pdf';
        Storage::disk('public')->put($fakePath, 'fake content');

        $file = UploadedFile::create([
            'original_name' => 'test.pdf',
            'stored_name'   => 'test-file.pdf',
            'mime_type'     => 'application/pdf',
            'size'          => 12,
            'disk'          => 'public',
            'path'          => $fakePath,
            'expires_at'    => Carbon::now()->addDays(7),
        ]);

        $this->delete(route('files.destroy', $file));

        Queue::assertPushed(
            SendFileDeletedNotificationJob::class,
            fn (SendFileDeletedNotificationJob $job): bool => $job->originalName === 'test.pdf'
        );
    }

    public function test_notification_job_sends_email_to_configured_address(): void
    {
        Mail::fake();

        config(['app.notify_email' => 'recipient@example.com']);

        $job = new SendFileDeletedNotificationJob('test.pdf', '2026-05-01 12:00:00');
        $job->handle();

        Mail::assertSent(
            FileDeletedMail::class,
            fn (FileDeletedMail $mail): bool => $mail->hasTo('recipient@example.com')
        );
    }
}
<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Jobs\SendFileDeletedNotificationJob;
use App\Mail\FileDeletedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class SendFileDeletedNotificationJobTest extends TestCase
{
    public function test_handle_sends_email_when_recipient_configured(): void
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

    public function test_handle_does_not_send_when_no_recipient(): void
    {
        Mail::fake();
        config(['app.notify_email' => '']);

        $job = new SendFileDeletedNotificationJob('test.pdf', '2026-05-01 12:00:00');
        $job->handle();

        Mail::assertNothingSent();
    }

    public function test_handle_logs_error_and_rethrows_on_mail_failure(): void
    {
        Log::spy();
        config(['app.notify_email' => 'recipient@example.com']);

        Mail::shouldReceive('to')
            ->once()
            ->andThrow(new RuntimeException('SMTP connection failed'));

        $job = new SendFileDeletedNotificationJob('test.pdf', '2026-05-01 12:00:00');

        $thrown = null;
        try {
            $job->handle();
        } catch (RuntimeException $e) {
            $thrown = $e;
        }

        $this->assertNotNull($thrown, 'Expected RuntimeException to be re-thrown.');
        $this->assertSame('SMTP connection failed', $thrown->getMessage());

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                \Mockery::pattern('/Failed to send/'),
                \Mockery::type('array')
            );
    }

    public function test_failed_logs_permanent_failure(): void
    {
        Log::spy();

        $job = new SendFileDeletedNotificationJob('test.pdf', '2026-05-01 12:00:00');
        $job->failed(new RuntimeException('permanent failure'));

        Log::shouldHaveReceived('error')
            ->once()
            ->with(
                \Mockery::pattern('/permanently failed/'),
                \Mockery::type('array')
            );
    }
}

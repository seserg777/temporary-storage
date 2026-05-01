<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendCleanupSummaryNotificationJob;
use App\Models\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FilesCleanupCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_cleanup_deletes_expired_files(): void
    {
        $this->createFileRecord('expired1.pdf', 'uploads/expired1.pdf', expired: true);
        $this->createFileRecord('expired2.pdf', 'uploads/expired2.pdf', expired: true);
        $fresh = $this->createFileRecord('fresh.pdf', 'uploads/fresh.pdf', expired: false);

        $this->artisan('files:cleanup')
            ->assertSuccessful()
            ->expectsOutputToContain('Deleted 2 expired file(s).');

        $this->assertDatabaseCount('uploaded_files', 1);
        $this->assertDatabaseHas('uploaded_files', ['id' => $fresh->id]);
    }

    public function test_cleanup_deletes_physical_files(): void
    {
        $path = 'uploads/expired.pdf';
        Storage::disk('public')->put($path, 'fake content');

        $this->createFileRecord('expired.pdf', $path, expired: true);

        $this->artisan('files:cleanup')->assertSuccessful();

        Storage::disk('public')->assertMissing($path);
    }

    public function test_cleanup_with_no_expired_files(): void
    {
        $this->createFileRecord('fresh.pdf', 'uploads/fresh.pdf', expired: false);

        $this->artisan('files:cleanup')
            ->assertSuccessful()
            ->expectsOutputToContain('Deleted 0 expired file(s).');

        $this->assertDatabaseCount('uploaded_files', 1);
    }

    public function test_cleanup_dispatches_single_summary_job_for_all_expired_files(): void
    {
        Queue::fake();

        $this->createFileRecord('expired1.pdf', 'uploads/expired1.pdf', expired: true);
        $this->createFileRecord('expired2.pdf', 'uploads/expired2.pdf', expired: true);
        $this->createFileRecord('fresh.pdf', 'uploads/fresh.pdf', expired: false);

        $this->artisan('files:cleanup')->assertSuccessful();

        Queue::assertPushed(SendCleanupSummaryNotificationJob::class, 1);

        Queue::assertPushed(
            SendCleanupSummaryNotificationJob::class,
            fn (SendCleanupSummaryNotificationJob $job): bool => count($job->deletedFiles) === 2
                && collect($job->deletedFiles)->pluck('original_name')->contains('expired1.pdf')
                && collect($job->deletedFiles)->pluck('original_name')->contains('expired2.pdf')
        );
    }

    public function test_cleanup_does_not_dispatch_summary_job_when_no_expired_files(): void
    {
        Queue::fake();

        $this->createFileRecord('fresh.pdf', 'uploads/fresh.pdf', expired: false);

        $this->artisan('files:cleanup')->assertSuccessful();

        Queue::assertNothingPushed();
    }

    private function createFileRecord(string $originalName, string $path, bool $expired): UploadedFile
    {
        return UploadedFile::create([
            'original_name' => $originalName,
            'stored_name' => basename($path),
            'mime_type' => 'application/pdf',
            'size' => 1024,
            'disk' => 'public',
            'path' => $path,
            'expires_at' => $expired
                ? Carbon::now()->subHour()
                : Carbon::now()->addHours(24),
        ]);
    }
}

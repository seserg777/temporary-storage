<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile as FakeFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_upload_is_allowed_within_rate_limit(): void
    {
        $ip = $this->uniqueIp();

        for ($i = 0; $i < 5; $i++) {
            $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');
            $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('files.store'), ['file' => $file]);

            $response->assertCreated();
        }
    }

    public function test_upload_is_blocked_after_rate_limit_exceeded(): void
    {
        $ip = $this->uniqueIp();

        for ($i = 0; $i < 5; $i++) {
            $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('files.store'), ['file' => $file]);
        }

        $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->postJson(route('files.store'), ['file' => $file]);

        $response->assertStatus(429);
        $response->assertJsonStructure(['message']);
    }

    public function test_rate_limit_response_includes_retry_after_header(): void
    {
        $ip = $this->uniqueIp();

        for ($i = 0; $i < 5; $i++) {
            $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');
            $this->withServerVariables(['REMOTE_ADDR' => $ip])
                ->post(route('files.store'), ['file' => $file]);
        }

        $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');
        $response = $this->withServerVariables(['REMOTE_ADDR' => $ip])
            ->post(route('files.store'), ['file' => $file]);

        $response->assertStatus(429);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    private function uniqueIp(): string
    {
        return '10.'.random_int(0, 255).'.'.random_int(0, 255).'.'.random_int(1, 254);
    }
}

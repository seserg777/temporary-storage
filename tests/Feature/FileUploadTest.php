<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile as FakeFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_index_page_loads_successfully(): void
    {
        $response = $this->get(route('files.index'));

        $response->assertOk();
        $response->assertViewIs('files.index');
    }

    public function test_can_upload_pdf_file(): void
    {
        $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->post(route('files.store'), ['file' => $file]);

        $response->assertCreated();
        $response->assertJsonStructure(['message', 'file' => ['id', 'original_name', 'size', 'expires_at']]);

        $this->assertDatabaseCount('uploaded_files', 1);
        $this->assertDatabaseHas('uploaded_files', ['original_name' => 'document.pdf']);

        /** @var UploadedFile $record */
        $record = UploadedFile::first();
        Storage::disk('public')->assertExists($record->path);
    }

    public function test_can_upload_docx_file(): void
    {
        $file = FakeFile::fake()->create(
            'document.docx',
            100,
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        );

        $response = $this->post(route('files.store'), ['file' => $file]);

        $response->assertCreated();
        $this->assertDatabaseHas('uploaded_files', ['original_name' => 'document.docx']);
    }

    public function test_upload_fails_with_no_file(): void
    {
        $response = $this->postJson(route('files.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_with_wrong_mime_type(): void
    {
        $file = FakeFile::fake()->create('malicious.txt', 10, 'text/plain');

        $response = $this->postJson(route('files.store'), ['file' => $file]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_with_wrong_extension(): void
    {
        // Correct MIME but wrong extension
        $file = FakeFile::fake()->create('document.exe', 10, 'application/pdf');

        $response = $this->postJson(route('files.store'), ['file' => $file]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_upload_fails_when_file_exceeds_10mb(): void
    {
        // 11 MB = 11 * 1024 KB
        $file = FakeFile::fake()->create('large.pdf', 11 * 1024, 'application/pdf');

        $response = $this->postJson(route('files.store'), ['file' => $file]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_uploaded_file_has_expires_at_set_to_24_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-04-30 12:00:00'));

        $file = FakeFile::fake()->create('document.pdf', 100, 'application/pdf');
        $this->post(route('files.store'), ['file' => $file]);

        /** @var UploadedFile $record */
        $record = UploadedFile::first();

        $this->assertEquals(
            '2026-05-01 12:00:00',
            $record->expires_at->format('Y-m-d H:i:s')
        );

        Carbon::setTestNow();
    }
}

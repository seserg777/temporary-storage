<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Tests\TestCase;

class CsrfTest extends TestCase
{
    public function test_csrf_mismatch_returns_419_json_for_ajax_request(): void
    {
        $request = Request::create('/files', 'POST', server: [
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $handler = $this->app->make(ExceptionHandler::class);
        $response = $handler->render($request, new TokenMismatchException);

        $this->assertSame(419, $response->getStatusCode());

        /** @var array<string, string> $data */
        $data = json_decode((string) $response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('message', $data);
        $this->assertStringContainsStringIgnoringCase('session', $data['message']);
    }

    public function test_csrf_mismatch_does_not_return_json_for_browser_request(): void
    {
        $request = Request::create('/files', 'POST', server: [
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml',
        ]);

        $handler = $this->app->make(ExceptionHandler::class);
        $response = $handler->render($request, new TokenMismatchException);

        $content = (string) $response->getContent();
        $decoded = json_decode($content, true);

        // Response should not be a JSON body with our custom message
        $this->assertTrue(
            $decoded === null || ! isset($decoded['message']) || ! str_contains(strtolower((string) $decoded['message']), 'session'),
            'Browser request should not receive JSON session-expired message.'
        );
    }
}

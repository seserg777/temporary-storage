<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Http\Requests\UploadFileRequest;
use App\Models\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FileController extends Controller
{
    public function __construct(
        private readonly FileStorageServiceInterface $fileStorageService,
    ) {}

    public function home(): View
    {
        return view('home');
    }

    public function index(): View
    {
        return view('files.index');
    }

    public function show(UploadedFile $file): View
    {
        return view('files.show', compact('file'));
    }

    public function store(UploadFileRequest $request): JsonResponse
    {
        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');

        $uploadedFile = $this->fileStorageService->store($file);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'file' => [
                'id' => $uploadedFile->id,
                'original_name' => $uploadedFile->original_name,
                'size' => $uploadedFile->size,
                'expires_at' => $uploadedFile->expires_at->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(UploadedFile $file, Request $request): RedirectResponse|JsonResponse
    {
        $this->fileStorageService->delete($file);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'File deleted successfully.']);
        }

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully.');
    }
}

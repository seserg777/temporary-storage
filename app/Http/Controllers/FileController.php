<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Http\Requests\UploadFileRequest;
use App\Models\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function store(UploadFileRequest $request): JsonResponse
    {
        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $request->file('file');

        $uploadedFile = $this->fileStorageService->store($file);

        return response()->json([
            'message' => 'File uploaded successfully.',
            'file'    => [
                'id'            => $uploadedFile->id,
                'original_name' => $uploadedFile->original_name,
                'size'          => $uploadedFile->size,
                'expires_at'    => $uploadedFile->expires_at->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(UploadedFile $file): RedirectResponse
    {
        $this->fileStorageService->delete($file);

        return redirect()->route('files.index')
            ->with('success', 'File deleted successfully.');
    }
}
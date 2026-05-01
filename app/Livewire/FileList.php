<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Models\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class FileList extends Component
{
    use WithPagination;

    #[On('fileUploaded')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        /** @var LengthAwarePaginator<UploadedFile> $files */
        $files = app(FileStorageServiceInterface::class)->paginate(20);

        return view('livewire.file-list', compact('files'));
    }
}

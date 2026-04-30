<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\FileStorageServiceInterface;
use App\Services\FileStorageService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            FileStorageServiceInterface::class,
            FileStorageService::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
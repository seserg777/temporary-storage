<?php

declare(strict_types=1);

use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FileController::class, 'home'])->name('home');

Route::prefix('files')->name('files.')->group(function (): void {
    Route::get('/', [FileController::class, 'index'])->name('index');
    Route::post('/', [FileController::class, 'store'])->name('store')->middleware('throttle:5,1');
    Route::get('/{file}', [FileController::class, 'show'])->name('show');
    Route::delete('/{file}', [FileController::class, 'destroy'])->name('destroy');
});

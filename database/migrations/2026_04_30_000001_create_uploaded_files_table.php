<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table): void {
            $table->id();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};

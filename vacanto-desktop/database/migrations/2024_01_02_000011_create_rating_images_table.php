<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rating_id')->constrained('freelancer_ratings')->cascadeOnDelete();
            $table->string('file_path', 500);
            $table->timestamp('created_at')->useCurrent();

            $table->index('rating_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_images');
    }
};

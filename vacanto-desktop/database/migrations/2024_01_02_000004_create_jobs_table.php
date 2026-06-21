<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->text('description');
            $table->string('location', 150)->nullable();
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship'])->default('full_time');
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->timestamps();

            $table->index('status');

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                $table->fullText(['title', 'description']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};

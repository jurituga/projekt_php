<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelancer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('freelancer_type', 50)->default('general');
            $table->text('bio')->nullable();
            $table->text('skills')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->string('avatar_path')->nullable();
            $table->string('government_id_ref', 100)->nullable();
            $table->string('government_id_path')->nullable();
            $table->text('qualifications')->nullable();
            $table->string('certification_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelancer_profiles');
    }
};

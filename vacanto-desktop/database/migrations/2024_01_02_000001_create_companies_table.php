<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('company_name', 200);
            $table->text('description')->nullable();
            $table->string('industry', 100)->nullable();
            $table->string('website')->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('business_registration_number', 100)->nullable();
            $table->string('tax_id_vat', 100)->nullable();
            $table->string('government_id_ref', 100)->nullable();
            $table->string('government_id_path')->nullable();
            $table->timestamps();

            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

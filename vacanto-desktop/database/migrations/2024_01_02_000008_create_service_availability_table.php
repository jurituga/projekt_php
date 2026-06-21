<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->date('available_date');
            $table->time('slot_time');
            $table->boolean('is_booked')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['service_id', 'available_date', 'slot_time']);
            $table->index('available_date');
            $table->index('is_booked');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_availability');
    }
};

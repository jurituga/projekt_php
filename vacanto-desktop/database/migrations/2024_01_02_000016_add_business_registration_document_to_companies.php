<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('business_registration_document_path')->nullable()->after('business_registration_number');
        });

        // Older registrations stored the business reg file in government_id_ref.
        DB::table('companies')
            ->whereNull('business_registration_document_path')
            ->whereNotNull('government_id_ref')
            ->where('government_id_ref', 'like', '%.%')
            ->update([
                'business_registration_document_path' => DB::raw('government_id_ref'),
                'government_id_ref' => null,
            ]);
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('business_registration_document_path');
        });
    }
};

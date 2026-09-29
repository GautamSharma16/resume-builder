<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (! Schema::hasColumn('templates', 'sample_data')) {
                $table->json('sample_data')->nullable()->after('pdf_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('templates', function (Blueprint $table) {
            if (Schema::hasColumn('templates', 'sample_data')) {
                $table->dropColumn('sample_data');
            }
        });
    }
};

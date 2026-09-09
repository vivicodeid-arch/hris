<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_details', function (Blueprint $table) {
            $table->decimal('skor', 12, 2)->default(0.00)->change();
        });

        Schema::table('kpi_employees', function (Blueprint $table) {
            $table->decimal('total_nilai', 12, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_details', function (Blueprint $table) {
            $table->decimal('skor', 12, 2)->nullable()->change();
        });

        Schema::table('kpi_employees', function (Blueprint $table) {
            $table->decimal('total_nilai', 12, 2)->nullable()->change();
        });
    }
};

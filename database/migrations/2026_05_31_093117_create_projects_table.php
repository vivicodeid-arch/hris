<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('kode_project', 20)->unique();
            $table->string('nama_project', 255);
            $table->text('deskripsi')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('project_categories')->onDelete('set null');
            $table->char('kode_dept', 3)->nullable();
            $table->foreign('kode_dept')->references('kode_dept')->on('departemen')->onDelete('set null')->cascadeOnUpdate();
            $table->char('kode_cabang', 3)->nullable();
            $table->foreign('kode_cabang')->references('kode_cabang')->on('cabang')->onDelete('set null')->cascadeOnUpdate();
            $table->string('created_by', 20);
            $table->foreign('created_by')->references('nik')->on('karyawan')->onDelete('cascade')->cascadeOnUpdate();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['planning', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('planning');
            $table->enum('prioritas', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->tinyInteger('progress')->unsigned()->default(0);
            $table->decimal('budget', 15, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('kode_dept');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};

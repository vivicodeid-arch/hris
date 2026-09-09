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
        Schema::create('project_task_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('project_tasks')->onDelete('cascade');
            $table->string('nik', 20);
            $table->foreign('nik')->references('nik')->on('karyawan')->onDelete('cascade')->cascadeOnUpdate();
            $table->string('nama_file', 255);
            $table->string('path', 500);
            $table->string('tipe_file', 50)->nullable();
            $table->bigInteger('ukuran')->unsigned()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_task_attachments');
    }
};

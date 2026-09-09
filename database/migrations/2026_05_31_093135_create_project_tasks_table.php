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
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('project_tasks')->onDelete('cascade');
            $table->string('kode_task', 30)->unique();
            $table->string('judul', 255);
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['todo', 'in_progress', 'review', 'completed', 'cancelled'])->default('todo');
            $table->enum('prioritas', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->tinyInteger('progress')->unsigned()->default(0);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('urutan')->default(0);
            $table->string('created_by', 20);
            $table->foreign('created_by')->references('nik')->on('karyawan')->onDelete('cascade')->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index('parent_id');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_tasks');
    }
};

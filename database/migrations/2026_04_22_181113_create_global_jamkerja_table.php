<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_jamkerja', function (Blueprint $table) {
            $table->id();
            $table->string('hari', 10);
            $table->char('kode_jam_kerja', 4)->nullable();
            $table->timestamps();
        });

        // Seed 7 hari default (tanpa jam kerja = libur)
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        foreach ($hari as $h) {
            DB::table('global_jamkerja')->insert([
                'hari' => $h,
                'kode_jam_kerja' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('global_jamkerja');
    }
};

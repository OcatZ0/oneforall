<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tata_letak_dasbor', function (Blueprint $table) {
            $table->id('id_tata_letak');
            $table->unsignedBigInteger('id_pengguna');
            $table->string('halaman', 50)->default('home');
            $table->json('tata_letak');

            $table->unique(['id_pengguna', 'halaman']);
            $table->foreign('id_pengguna')
                  ->references('id_pengguna')
                  ->on('pengguna')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tata_letak_dasbor');
    }
};

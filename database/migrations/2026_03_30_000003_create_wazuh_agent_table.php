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
        Schema::create('wazuh_agent', function (Blueprint $table) {
            $table->string('id_agent', 255)->primary();
            $table->string('nama', 100);
            $table->string('deskripsi', 255);
            $table->dateTime('tanggal_dibuat')->useCurrent();
            $table->unsignedBigInteger('id_pengguna');
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wazuh_agent');
    }
};

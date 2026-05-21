<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_layouts', function (Blueprint $table) {
            // Drop FK before touching the unique index it relies on
            $table->dropForeign(['id_pengguna']);
            $table->dropUnique(['id_pengguna']);

            $table->string('page', 50)->default('home')->after('id_pengguna');
            $table->unique(['id_pengguna', 'page']);

            // Re-add FK
            $table->foreign('id_pengguna')
                  ->references('id_pengguna')
                  ->on('pengguna')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_layouts', function (Blueprint $table) {
            $table->dropForeign(['id_pengguna']);
            $table->dropUnique(['id_pengguna', 'page']);
            $table->dropColumn('page');
            $table->unique(['id_pengguna']);
            $table->foreign('id_pengguna')
                  ->references('id_pengguna')
                  ->on('pengguna')
                  ->onDelete('cascade');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_token', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_token', function (Blueprint $table) {
            $table->dateTime('created_at')->nullable()->change();
        });
    }
};

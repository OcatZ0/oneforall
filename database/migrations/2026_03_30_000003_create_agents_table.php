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
            $table->string('agent_id', 255)->primary();
            $table->string('name', 100);
            $table->string('description', 255);
            $table->dateTime('created_at')->useCurrent();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('user')->nullOnDelete();
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET GLOBAL event_scheduler = ON');

        DB::statement("
            CREATE EVENT IF NOT EXISTS cleanup_old_activity_logs
            ON SCHEDULE EVERY 1 DAY
            STARTS CURRENT_TIMESTAMP
            DO
              DELETE FROM activity_logs
              WHERE created_at < NOW() - INTERVAL 1 YEAR
        ");
    }

    public function down(): void
    {
        DB::statement('DROP EVENT IF EXISTS cleanup_old_activity_logs');
    }
};

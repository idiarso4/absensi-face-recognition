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
        // Add indexes to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'attendances_user_date_index');
            $table->index('created_at', 'attendances_created_at_index');
            $table->index('start_time', 'attendances_start_time_index');
            $table->index('end_time', 'attendances_end_time_index');
            $table->index('is_leave', 'attendances_is_leave_index');
        });

        // Add indexes to leaves table
        Schema::table('leaves', function (Blueprint $table) {
            $table->index(['user_id', 'status'], 'leaves_user_status_index');
            $table->index('status', 'leaves_status_index');
            $table->index('start_date', 'leaves_start_date_index');
            $table->index('end_date', 'leaves_end_date_index');
            $table->index('created_at', 'leaves_created_at_index');
        });

        // Add indexes to users table
        Schema::table('users', function (Blueprint $table) {
            $table->index('name', 'users_name_index');
            $table->index('created_at', 'users_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_user_date_index');
            $table->dropIndex('attendances_created_at_index');
            $table->dropIndex('attendances_start_time_index');
            $table->dropIndex('attendances_end_time_index');
            $table->dropIndex('attendances_is_leave_index');
        });

        // Drop indexes from leaves table
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropIndex('leaves_user_status_index');
            $table->dropIndex('leaves_status_index');
            $table->dropIndex('leaves_start_date_index');
            $table->dropIndex('leaves_end_date_index');
            $table->dropIndex('leaves_created_at_index');
        });

        // Drop indexes from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_created_at_index');
        });
    }
};

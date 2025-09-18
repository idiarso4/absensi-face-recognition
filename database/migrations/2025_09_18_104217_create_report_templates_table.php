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
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // attendance, leave, monthly-summary
            $table->text('description')->nullable();
            $table->json('filters')->nullable(); // stored filter configuration
            $table->json('recipients')->nullable(); // email recipients
            $table->string('schedule_frequency')->default('manual'); // manual, daily, weekly, monthly
            $table->string('schedule_day')->nullable(); // for weekly: monday, tuesday, etc.
            $table->string('schedule_time')->nullable(); // HH:MM format
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
    }
};

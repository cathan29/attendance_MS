<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_assignment_id')->constrained('class_assignments')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 50)->nullable();
            $table->timestamps();

            $table->unique(['class_assignment_id', 'day_of_week', 'start_time'], 'unique_class_schedule_slot');
            $table->index(['day_of_week', 'start_time']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('class_schedule_id')->nullable()->after('subject_id')->constrained('class_schedules')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_schedule_id');
        });

        Schema::dropIfExists('class_schedules');
    }
};

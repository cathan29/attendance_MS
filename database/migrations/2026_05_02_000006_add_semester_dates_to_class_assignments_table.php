<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('class_assignments', function (Blueprint $table) {
            $table->date('semester_start_date')->nullable()->after('semester');
            $table->date('semester_end_date')->nullable()->after('semester_start_date');
        });

        foreach (config('school.semesters', []) as $semester => $dates) {
            DB::table('class_assignments')
                ->where('semester', $semester)
                ->update([
                    'semester_start_date' => $dates['start_date'] ?? null,
                    'semester_end_date' => $dates['end_date'] ?? null,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('class_assignments', function (Blueprint $table) {
            $table->dropColumn(['semester_start_date', 'semester_end_date']);
        });
    }
};

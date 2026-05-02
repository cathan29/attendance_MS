<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('strand_id')->constrained('strands')->cascadeOnDelete();
            $table->enum('year_level', ['11', '12']);
            $table->string('section', 50);
            $table->string('school_year', 20)->default('2025-2026');
            $table->string('semester', 20)->default('1st Semester');
            $table->timestamps();
            $table->unique(['teacher_id', 'subject_id', 'strand_id', 'year_level', 'section', 'school_year', 'semester'], 'unique_class_assignment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_assignments');
    }
};

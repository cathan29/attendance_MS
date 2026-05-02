<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('strand_id')->constrained('strands')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('year_level', ['11', '12']);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['strand_id', 'subject_id', 'year_level'], 'unique_curriculum_subject');
            $table->index(['strand_id', 'year_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_subjects');
    }
};

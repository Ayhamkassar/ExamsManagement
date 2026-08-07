<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_unit_subject', function (Blueprint $table) {
            $table->foreignUlid('academic_unit_id')->constrained('academic_units')->cascadeOnDelete();
            $table->foreignUlid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUlid('academic_year_id')->nullable()->constrained('academic_years')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['academic_unit_id', 'subject_id', 'academic_year_id'], 'academic_unit_subject_primary');
            $table->index(['academic_year_id']);
            $table->index(['subject_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_unit_subject');
    }
};

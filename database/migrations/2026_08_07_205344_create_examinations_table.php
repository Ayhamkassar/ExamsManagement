<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examinations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('examination_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('academic_unit_id')->nullable()->constrained('academic_units')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->integer('passing_marks')->nullable();
            $table->string('status');
            $table->json('configuration')->nullable();
            $table->foreignUlid('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'examination_cycle_id', 'code'], 'examinations_tenant_cycle_code_unique');
            $table->index(['tenant_id', 'examination_cycle_id']);
            $table->index(['tenant_id', 'subject_id']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examinations');
    }
};

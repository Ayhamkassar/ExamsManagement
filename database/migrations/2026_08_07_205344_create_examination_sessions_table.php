<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examination_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('examination_id')->constrained()->cascadeOnDelete();
            $table->string('session_code');
            $table->dateTime('scheduled_start_at');
            $table->dateTime('scheduled_end_at');
            $table->string('timezone');
            $table->string('location_name')->nullable();
            $table->json('location_metadata')->nullable();
            $table->string('status');
            $table->integer('capacity')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'examination_id', 'session_code'], 'examination_sessions_tenant_exam_code_unique');
            $table->index(['tenant_id', 'examination_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['scheduled_start_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examination_sessions');
    }
};

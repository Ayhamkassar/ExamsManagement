<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code'], 'subjects_unique_code_per_tenant')->whereNull('deleted_at');
            $table->index(['tenant_id', 'status']);
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

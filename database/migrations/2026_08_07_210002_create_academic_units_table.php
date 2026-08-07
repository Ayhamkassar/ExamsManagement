<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_units', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('academic_units')->nullOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('code')->nullable();
            $table->json('metadata')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'parent_id']);
            $table->index(['tenant_id', 'status']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_units');
    }
};

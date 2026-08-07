<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('type')->default('school')->after('slug');
            $table->string('logo_path')->nullable()->after('type');
            $table->foreignUlid('created_by')->nullable()->after('logo_path');
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['legal_name', 'type', 'logo_path', 'created_by']);
            $table->dropIndex(['type', 'status']);
        });
    }
};

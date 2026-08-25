<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Replace tenant_id (old tenants table) with customer_id (users table)
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');

            $table->foreignId('customer_id')
                ->after('id')
                ->constrained('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');

            $table->foreignId('tenant_id')
                ->after('id')
                ->constrained('tenants')
                ->restrictOnDelete();
        });
    }
};

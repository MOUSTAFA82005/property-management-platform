<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('contracts', 'tenant_id') && !Schema::hasColumn('contracts', 'user_id')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->renameColumn('tenant_id', 'user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contracts', 'user_id') && !Schema::hasColumn('contracts', 'tenant_id')) {
            Schema::table('contracts', function (Blueprint $table) {
                $table->renameColumn('user_id', 'tenant_id');
            });
        }
    }
};

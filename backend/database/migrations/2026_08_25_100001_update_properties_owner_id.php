<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Rename manager_id to owner_id to match new Owner/Customer architecture
            $table->dropForeign(['manager_id']);
            $table->renameColumn('manager_id', 'owner_id');
            $table->foreign('owner_id')->references('id')->on('users')->restrictOnDelete();

            // Add published status for marketplace visibility
            $table->boolean('is_published')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->renameColumn('owner_id', 'manager_id');
            $table->foreign('manager_id')->references('id')->on('users')->restrictOnDelete();
            $table->dropColumn('is_published');
        });
    }
};

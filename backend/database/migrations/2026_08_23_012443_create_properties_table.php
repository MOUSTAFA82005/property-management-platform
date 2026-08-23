<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->text('address');

            $table->string('city');

            $table->text('description')->nullable();

            $table->string('property_type');

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->foreignId('manager_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
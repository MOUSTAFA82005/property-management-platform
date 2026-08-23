<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('building_id')
                ->constrained('buildings')
                ->cascadeOnDelete();

            $table->string('unit_number');

            $table->unsignedInteger('floor')->default(0);

            $table->string('unit_type');

            $table->decimal('area', 10, 2)->nullable();

            $table->unsignedTinyInteger('bedrooms')->default(0);

            $table->unsignedTinyInteger('bathrooms')->default(0);

            $table->decimal('monthly_rent', 10, 2);

            $table->enum('status', [
                'available',
                'occupied',
                'reserved',
            ])->default('available');

            $table->timestamps();

            $table->unique(['building_id', 'unit_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
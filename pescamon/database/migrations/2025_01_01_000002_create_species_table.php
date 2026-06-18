<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('scientific_name');
            $table->string('slug')->unique();
            $table->text('habitat_description')->nullable();
            $table->string('image_locked_path')->nullable();
            $table->string('image_reference_path')->nullable();
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'legendary'])->default('common');
            $table->decimal('average_weight_kg', 8, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species');
    }
};

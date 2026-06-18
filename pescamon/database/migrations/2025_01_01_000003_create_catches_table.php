<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->decimal('weight_kg', 8, 3);
            $table->decimal('length_cm', 8, 2)->nullable();
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('location_name')->nullable();
            $table->dateTime('caught_at');
            $table->enum('method', ['spinning', 'fly', 'bait_casting', 'trolling', 'other'])->default('other');
            $table->string('bait_used')->nullable();
            $table->enum('water_body_type', ['river', 'lake', 'sea', 'reservoir', 'other'])->default('other');
            $table->json('exif_raw')->nullable();
            $table->boolean('is_personal_best')->default(false);
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'species_id']);
            $table->index('status');
            $table->index('caught_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catches');
    }
};

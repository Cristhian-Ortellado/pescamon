<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('species_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('species_id')->constrained()->cascadeOnDelete();
            $table->string('region');
            $table->date('closed_start_date');
            $table->date('closed_end_date');
            $table->timestamps();

            $table->index(['species_id', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('species_seasons');
    }
};

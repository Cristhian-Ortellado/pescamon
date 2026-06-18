<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('level')->default(1)->after('email');
            $table->unsignedInteger('xp')->default(0)->after('level');
            $table->enum('location_privacy', ['exact', 'approximate', 'hidden'])->default('approximate')->after('xp');
            $table->enum('unit_preference', ['metric', 'imperial'])->default('metric')->after('location_privacy');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['level', 'xp', 'location_privacy', 'unit_preference']);
        });
    }
};

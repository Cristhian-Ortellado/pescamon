<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catches', function (Blueprint $table) {
            $table->unsignedInteger('xp_earned')->default(0)->after('is_personal_best');
        });
    }

    public function down(): void
    {
        Schema::table('catches', function (Blueprint $table) {
            $table->dropColumn('xp_earned');
        });
    }
};

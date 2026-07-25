<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Move any existing single image into the new table as position 0.
        DB::table('products')->whereNotNull('image_path')->orderBy('id')
            ->each(function ($product) {
                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'path' => $product->image_path,
                    'position' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('price');
        });

        // Restore the first image back onto the product row.
        DB::table('product_images')->where('position', 0)->orderBy('product_id')
            ->each(function ($image) {
                DB::table('products')->where('id', $image->product_id)
                    ->update(['image_path' => $image->path]);
            });

        Schema::dropIfExists('product_images');
    }
};

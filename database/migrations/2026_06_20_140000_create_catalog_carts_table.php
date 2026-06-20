<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One catalog cart per user (no platform grouping — catalog products are
        // merchant-owned, unlike the platform-scoped scraped cart). Unique user_id
        // makes firstOrCreate race-safe.
        Schema::create('catalog_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_carts');
    }
};

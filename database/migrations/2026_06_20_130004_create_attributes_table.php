<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Store-wide attribute library: an axis of choice ("اللون", "التخزين")
        // defined once and referenced by every catalog product that uses it.
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "اللون", "التخزين"
            $table->integer('position')->default(0);
            // Marks a colour axis whose values carry hex swatches. The storefront
            // renders colour chips only when this is set.
            $table->boolean('is_color')->default(false);
            $table->timestamps();

            $table->unique('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};

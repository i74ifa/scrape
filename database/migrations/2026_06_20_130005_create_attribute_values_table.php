<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A choice on an axis ("أحمر" on "اللون"), defined once and shared across
        // products. The swatch (color hex) lives here, authored once.
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // "أحمر", "128GB"
            $table->integer('position')->default(0);
            $table->string('color')->nullable(); // hex swatch, e.g. "#ff0000"
            $table->timestamps();

            $table->unique(['attribute_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_values');
    }
};

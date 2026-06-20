<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Which store-wide attribute values make up a variant
        // (variant ↔ attribute_value).
        Schema::create('attribute_value_variant', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained()->cascadeOnDelete();

            $table->primary(['product_variant_id', 'attribute_value_id'], 'avv_primary');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_variant');
    }
};

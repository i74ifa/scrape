<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A line in the catalog cart: a catalog product + an optional variant +
        // quantity. Price is computed live from the product/variant at read time,
        // so the cart always reflects current pricing (snapshots happen at
        // checkout). The unique key lets the same product+variant stack quantity
        // instead of duplicating.
        Schema::create('catalog_cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_cart_id')->constrained('catalog_carts')->cascadeOnDelete();
            $table->foreignId('catalog_product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['catalog_cart_id', 'catalog_product_id', 'product_variant_id'], 'catalog_cart_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_cart_items');
    }
};

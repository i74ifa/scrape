<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalog product ↔ category pivot. Named explicitly (rather than the
        // default category_product) so it never collides with the scraped
        // product taxonomy and clearly points at catalog_products.
        Schema::create('category_catalog_product', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('catalog_product_id')->constrained('catalog_products')->cascadeOnDelete();

            $table->primary(['category_id', 'catalog_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_catalog_product');
    }
};

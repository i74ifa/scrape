<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalog products live in their OWN table, separate from the scraped
        // `products` table (which powers cart/orders/scraping). This is the
        // merchant-authored catalog: brands, categories, variants, gallery.
        Schema::create('catalog_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('name', 255);
            $table->json('name_translations')->nullable();
            $table->string('short_description', 255)->nullable();
            $table->text('description')->nullable();
            $table->json('description_translations')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->timestamp('end_discount_date')->nullable();
            $table->string('weight')->nullable();
            $table->string('sku', 30)->nullable();
            $table->string('slug');
            $table->string('promotion')->nullable();
            $table->json('specifications')->nullable(); // [{ key, value }]
            $table->string('tags')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('views')->default(0);
            $table->boolean('has_variants')->default(false);
            $table->boolean('is_digital')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('slug');
            $table->index('sku');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_products');
    }
};

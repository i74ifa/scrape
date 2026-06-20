<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // An immutable line in a placed order. Name, variant label, and unit price
        // are SNAPSHOTTED at checkout so the order is stable even if the catalog
        // product/variant is later edited or deleted. The product/variant FKs are
        // nullable + nullOnDelete (kept only for "buy again" / linking).
        Schema::create('catalog_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_order_id')->constrained('catalog_orders')->cascadeOnDelete();
            $table->foreignId('catalog_product_id')->nullable()->constrained('catalog_products')->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('name'); // product name snapshot
            $table->string('variant_label')->nullable(); // e.g. "أحمر / 128GB"
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_order_items');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A placed catalog order. Address is kept both as an FK (nullOnDelete so
        // deleting an address never deletes order history) and a JSON snapshot
        // (address_raw) so the shipping details survive address edits/deletes —
        // mirrors the scraped checkout_orders pattern.
        Schema::create('catalog_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->json('address_raw')->nullable();
            $table->string('status')->default('pending'); // App\Enums\CatalogOrderStatus
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedInteger('total_quantity')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_orders');
    }
};

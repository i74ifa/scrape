<?php

use App\Enums\OrderStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->decimal('local_shipping', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('shipping', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->enum('status', OrderStatus::toArray())->default(OrderStatus::PENDING->value);
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('payment_reference')->nullable();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained('platforms')->cascadeOnDelete();
            $table->foreignId('checkout_order_id')->constrained('checkout_orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

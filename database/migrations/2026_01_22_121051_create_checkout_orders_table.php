<?php

use App\Models\User;
use App\Enums\CheckoutOrderStatus;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('checkout_orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', CheckoutOrderStatus::toArray())->default(CheckoutOrderStatus::PENDING_PAYMENT->value);
            $table->decimal('local_shipping', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('discount', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('payment_reference')->nullable();

            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkout_orders');
    }
};

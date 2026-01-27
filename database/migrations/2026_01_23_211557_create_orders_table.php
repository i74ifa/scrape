<?php

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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->decimal('tax', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('shipping', 10, 2);
            $table->decimal('sub_total', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->enum('status', ['pending', 'approved', 'paid', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->string('payment_method');
            $table->string('payment_status');
            $table->string('payment_reference')->nullable();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('address_id')->constrained('addresses')->cascadeOnDelete();
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

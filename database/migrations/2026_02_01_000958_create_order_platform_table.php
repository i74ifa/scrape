<?php

use App\Models\Order;
use App\Models\Platform;
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
        Schema::create('order_platform', function (Blueprint $table) {
            $table->string('identifier');
            $table->decimal('shipping_amount', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('subtotal_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);

            $table->foreignIdFor(Order::class)->constrained('orders')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->constrained('platforms')->cascadeOnDelete();
            $table->primary(['order_id', 'platform_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_platform');
    }
};

<?php

use App\Models\Address;
use App\Models\User;
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
        Schema::create('cart_bundles', function (Blueprint $table) {
            $table->id();
            $table->decimal('subtotal', 18, 6);
            $table->decimal('tax', 18, 6);
            $table->decimal('shipping', 18, 6);
            $table->decimal('local_shipping', 18, 6);
            $table->decimal('total', 18, 6);
            $table->decimal('discount', 18, 6);
            $table->foreignIdFor(Address::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_bundles');
    }
};

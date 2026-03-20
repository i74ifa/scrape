<?php

use App\Models\User;
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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->decimal('total', 18, 6);
            $table->decimal('subtotal', 18, 6);
            $table->decimal('tax', 18, 6);
            $table->decimal('shipping', 18, 6);
            $table->decimal('discount', 18, 6);
            $table->boolean('is_delivery_to_home')->default(false);
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

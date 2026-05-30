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
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('total_quantity')->default(0)->after('grand_total');
        });

        Schema::table('checkout_orders', function (Blueprint $table) {
            $table->integer('total_quantity')->default(0)->after('grand_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_quantity');
        });

        Schema::table('checkout_orders', function (Blueprint $table) {
            $table->dropColumn('total_quantity');
        });
    }
};

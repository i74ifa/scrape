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
        Schema::table('checkout_orders', function (Blueprint $table) {
            // remove address
            if (Schema::hasColumn('checkout_orders', 'address')) {
                $table->dropColumn('address');
            }

            $table->json('address_raw')->nullable()->after('address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checkout_orders', function (Blueprint $table) {
            $table->json('address')->nullable()->after('address_id');

            if (Schema::hasColumn('checkout_orders', 'address_raw')) {
                $table->dropColumn('address_raw');
            }
        });
    }
};

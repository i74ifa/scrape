<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mirror the scraped checkout_orders payment shape so catalog orders can
        // capture a bank transfer (banks_transfer) at checkout — payment_method
        // holds the gateway enum value, payment_reference stores the JSON payment
        // payload (bank_id, iban, receipt image path, ...).
        Schema::table('catalog_orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
            $table->json('payment_reference')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_reference']);
        });
    }
};

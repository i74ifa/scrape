<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * A missing unique index on cart_bundles.user_id let getActiveCartBundle()'s
     * firstOrCreate() create duplicate bundles under concurrent requests. Checkout
     * only deletes the active bundle, so the stale duplicate (and its carts) would
     * resurface as a "returning" cart after an order was placed. Dedupe first, then
     * enforce one bundle per user so firstOrCreate() becomes race-safe.
     */
    public function up(): void
    {
        // Keep the most recent bundle per user, drop the rest (their carts/items
        // are removed via the carts -> cart_items FK cascade).
        $duplicates = DB::table('cart_bundles')
            ->select('user_id', DB::raw('MAX(id) as keep_id'))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $staleIds = DB::table('cart_bundles')
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            DB::table('carts')->whereIn('cart_bundle_id', $staleIds)->delete();
            DB::table('cart_bundles')->whereIn('id', $staleIds)->delete();
        }

        Schema::table('cart_bundles', function (Blueprint $table) {
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_bundles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catalog category tree (separate from the scraped-product taxonomy).
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->json('name_translations')->nullable();
            $table->string('slug');
            $table->string('image')->nullable();
            // Materialized path of ANCESTOR ids (excluding self), e.g. '/' for a
            // root, '/1/4/' for a node under 1 → 4. Lets us fetch a whole subtree
            // with one indexed LIKE query — no recursion, no N+1. `depth` =
            // number of ancestors.
            $table->string('path')->default('/')->index();
            $table->unsignedSmallInteger('depth')->default(0);
            $table->timestamps();

            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

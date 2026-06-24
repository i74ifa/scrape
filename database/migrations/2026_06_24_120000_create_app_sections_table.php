<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // App "pages" (home / products / any custom slug) are an ordered list of
        // sections. Each section has a type (`name`) and a free-form `content`
        // payload mirroring the shape the Flutter components renderer expects.
        Schema::create('app_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page')->index();        // home, products, or custom slug
            $table->string('name');                  // BannerSwipe, BannerGrid, CustomBanner, ProductSwipe, ProductGrid
            $table->json('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['page', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_sections');
    }
};

<?php

use App\Models\Platform;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image');
            $table->decimal('price', 8, 2); // usd
            $table->string('variants')->nullable();
            $table->string('description')->nullable();
            $table->text('url');
            $table->integer('weight');
            $table->string('brand')->nullable();
            $table->string('category')->nullable();


            $table->foreignIdFor(Platform::class)->constrained();
            $table->foreignId('user_id')->constrained();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

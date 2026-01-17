<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->index();
            $table->string('name');
            $table->string('image');
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_buy_always')->default(true);
            $table->string('sku');
            $table->double('base_cost');
            $table->double('retail_cost');
            $table->double('wholesale_cost');
            $table->double('in_stock');
            $table->double('sold');
            $table->double('temporality');
            $table->double('available');
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

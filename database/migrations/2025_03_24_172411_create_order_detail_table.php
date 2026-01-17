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
        Schema::create('order_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Order::class)->index();
            $table->foreignIdFor(\App\Models\Product::class)->index();
            $table->double('quantity')->nullable();
            $table->double('base_cost')->nullable();
            $table->double('wholesale_cost')->nullable();
            $table->double('retail_cost')->nullable();
            $table->double('entry_cost')->nullable();
            $table->double('vat')->default(0);
            $table->double('vat_cost')->default(0);
            $table->string('unit')->nullable();
            $table->double('discount')->nullable();
            $table->boolean('discount_type')->default(1)->comment('1: %, 2: Số tiền');
            $table->double('base_cost_base')->nullable();
            $table->double('retail_cost_base')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_detail');
    }
};

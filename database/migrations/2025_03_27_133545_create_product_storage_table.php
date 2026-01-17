<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_storage', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Product::class);
            $table->integer('user_id')->nullable();
            $table->smallInteger('type')->default(1); //3:Khơi tạo sản phẩm, 2:nhập hàng vào kho, 1: xuất hàng
            $table->integer('order_id')->nullable();
            $table->integer('quantity_change')->default(0);
            $table->integer('quantity')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_storage');
    }
};

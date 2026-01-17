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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->index();
            $table->foreignIdFor(\App\Models\Customer::class)->index()->nullable();
            $table->foreignIdFor(\App\Models\Supplier::class)->index()->nullable();
            $table->integer('phone')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('email')->nullable();
            $table->double('base_cost')->nullable();
            $table->double('retail_cost')->nullable();
            $table->double('wholesale_cost')->nullable();
            $table->double('base_code_base')->nullable();
            $table->double('retail_code_base')->nullable();
            $table->double('entry_cost')->nullable();
            $table->double('vat')->nullable();
            $table->smallInteger('type')->default(1)->comment('1: Đơn bán, 2: Đơn nhập');
            $table->integer('status')->default(2)->comment('1: Chờ xác nhận, 2: Hoàn thành, 3: Đã hủy, 4: Trả hàng' );
            $table->string('code')->nullable();
            $table->double('discount')->nullable();
            $table->smallInteger('discount_type')->default(1)->comment('1: %, 2: Số tiền');
            $table->string('payment_type')->nullable();
            $table->boolean('active')->default(1)->comment('1: Active, 0: Inactive');
            $table->integer('order_id')->nullable()->comment(' order id base refund');
            $table->boolean('is_refund')->default(0)->comment('0: Không hoàn, 1: Hoàn');
            $table->boolean('is_retail')->default(1)->comment('0: Sỉ, 1: Lẻ');
            $table->date('create_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

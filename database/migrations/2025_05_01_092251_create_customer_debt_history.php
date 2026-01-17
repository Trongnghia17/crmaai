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
        Schema::create('customer_debt_history', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\CustomerDebtHistory::class)->index();
            $table->foreignIdFor(\App\Models\User::class)->index();
            $table->foreignIdFor(\App\Models\Order::class)->nullable()->index();
            $table->double('price')->default(0)->nullable();
            $table->double('previous_debt')->default(0)->nullable();
            $table->double('remaining_debt')->default(0)->nullable();
            $table->string('note')->nullable();
            $table->integer('type')->default(1)->comment('1: thanh toán qua tạo đơn, 2: thanh toán qua tạo phiếu thu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_debt_history');
    }
};

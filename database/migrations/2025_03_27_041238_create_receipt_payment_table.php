<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receipt_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('partner_group_id'); // 1 ncc, 2 khach hang
            $table->string('partner_group_name');
            $table->integer('partner_id');
            $table->string('partner_name');
            $table->foreignIdFor(Order::class)->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_edit')->default(true);
            $table->smallInteger('type')->default(1); //1: thu, 2: chi
            $table->double('price')->default(0);
            $table->string('payment_type', 10)->nullable();
            $table->boolean('is_other_income')->default(false);
            $table->longText('note')->nullable();
            $table->foreignIdFor(User::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_payment');
    }
};

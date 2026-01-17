<?php

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
        Schema::create('order_payment', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Order::class)->index()->constrained()->cascadeOnDelete();
            $table->double('price')->default(0);
            $table->foreignIdFor(User::class, 'user_id')->index();
            $table->unsignedTinyInteger('type')->default(1)->comment('1: Tiền mặt, 2: Chuyển khoản, 3: Thẻ');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_payment');
    }
};

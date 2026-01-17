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
        Schema::create('profit_loss', function (Blueprint $table) {
            $table->id();
            $table->double('revenue_sale')->default(0);
            $table->double('discount_sale')->default(0);
            $table->double('order_cancel')->default(0);
            $table->double('cost_sale')->default(0);
            $table->double('fee')->default(0);
            $table->double('other_income')->default(0);
            $table->double('other_expense')->default(0);
            $table->foreignIdFor(User::class, 'user_id')->index();
            $table->date('time');
            $table->double('vat')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profit_loss');
    }
};

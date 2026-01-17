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
        Schema::table('receipt_payment', function (Blueprint $table) {
            $table->unsignedBigInteger('receipt_type_id')->after('order_id')->nullable()->comment('ID loại biên lai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_payment', function (Blueprint $table) {
            $table->dropColumn('receipt_type_id');
        });
    }
};

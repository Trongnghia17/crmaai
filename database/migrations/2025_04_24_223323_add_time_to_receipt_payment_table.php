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
            $table->date('time')->after('created_at')->nullable()->comment('Thời gian tạo biên lai');
            $table->integer('partner_group_id')->nullable()->change(); // 1 ncc, 2 khach hang
            $table->string('partner_group_name')->nullable()->change();
            $table->integer('partner_id')->nullable()->change();
            $table->string('partner_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_payment', function (Blueprint $table) {
            $table->dropColumn('time');
        });
    }
};

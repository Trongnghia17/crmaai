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
        Schema::table('customers', function (Blueprint $table) {
            $table->double('debt_old')->nullable()->default(0)->after('total_money');
            $table->double('pay_debt_old')->nullable()->default(0)->after('debt_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('debt_old');
            $table->dropColumn('pay_debt_old');
        });
    }
};

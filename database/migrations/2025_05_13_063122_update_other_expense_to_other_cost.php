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
        Schema::table('profit_loss', function (Blueprint $table) {
            $table->renameColumn('other_expense', 'other_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_loss', function (Blueprint $table) {
            $table->renameColumn('other_cost', 'other_expense');
        });
    }
};

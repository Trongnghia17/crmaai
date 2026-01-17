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
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('base_code_base','base_cost_base');
            $table->renameColumn('retail_code_base', 'retail_cost_base');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('base_cost_base', 'retail_code_base');
            $table->renameColumn('retail_cost_base', 'retail_code_base');

        });
    }
};

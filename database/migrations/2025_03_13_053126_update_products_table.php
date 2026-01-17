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
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->string('sku')->nullable()->change();
            $table->double('base_cost')->default(0)->change();
            $table->double('retail_cost')->default(0)->change();
            $table->double('wholesale_cost')->default(0)->change();
            $table->double('in_stock')->default(0)->change();
            $table->double('sold')->default(0)->change();
            $table->double('temporality')->default(0)->change();
            $table->double('available')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable(false)->change();
            $table->text('description')->nullable(false)->change();
            $table->string('sku')->nullable(false)->change();
            $table->double('base_cost')->default(null)->change();
            $table->double('retail_cost')->default(null)->change();
            $table->double('wholesale_cost')->default(null)->change();
            $table->double('in_stock')->default(null)->change();
            $table->double('sold')->default(null)->change();
            $table->double('temporality')->default(null)->change();
            $table->double('available')->default(null)->change();
        });
    }
};

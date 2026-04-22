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
        Schema::create('companies', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('lat', 10, 7); // vĩ độ
        $table->decimal('lng', 10, 7); // kinh độ
        $table->integer('radius')->default(100); // bán kính (m)
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

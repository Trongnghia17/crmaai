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
        Schema::create('receipt_type', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Tên loại biên lai');
            $table->boolean('status')->default(1)->comment('Trạng thái');
            $table->foreignIdFor(\App\Models\User::class)->index();
            $table->unsignedTinyInteger('type')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_type');
    }
};

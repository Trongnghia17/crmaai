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
        Schema::create('attendance_logs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        $table->enum('type', ['check_in', 'check_out']);

        $table->decimal('lat', 10, 7);
        $table->decimal('lng', 10, 7);

        $table->string('image')->nullable();
        $table->float('distance')->nullable();

        $table->string('device')->nullable(); // device info
        $table->string('ip')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};

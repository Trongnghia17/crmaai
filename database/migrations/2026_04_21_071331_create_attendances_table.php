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
        Schema::create('attendances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();

        // ngày chấm công (quan trọng để query)
        $table->date('work_date');

        // CHECK IN
        $table->timestamp('check_in_time')->nullable();
        $table->decimal('check_in_lat', 10, 7)->nullable();
        $table->decimal('check_in_lng', 10, 7)->nullable();
        $table->string('check_in_image')->nullable();
        $table->float('check_in_distance')->nullable();

        // CHECK OUT
        $table->timestamp('check_out_time')->nullable();
        $table->decimal('check_out_lat', 10, 7)->nullable();
        $table->decimal('check_out_lng', 10, 7)->nullable();
        $table->string('check_out_image')->nullable();
        $table->float('check_out_distance')->nullable();

        // trạng thái
        $table->enum('status', ['normal', 'late', 'early_leave', 'absent'])->default('normal');

        $table->timestamps();

        // mỗi ngày 1 bản ghi
        $table->unique(['user_id', 'work_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

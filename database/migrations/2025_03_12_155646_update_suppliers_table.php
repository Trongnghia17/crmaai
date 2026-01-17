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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('contact_person')->nullable()->after('address');
            $table->string('contact_person_phone')->nullable()->after('contact_person');
            $table->string('surrogate')->nullable()->after('contact_person_phone');
            $table->string('note')->nullable()->after('surrogate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('phone')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->dropColumn('contact_person');
            $table->dropColumn('contact_person_phone');
            $table->dropColumn('surrogate');
            $table->dropColumn('note');
        });
    }
};

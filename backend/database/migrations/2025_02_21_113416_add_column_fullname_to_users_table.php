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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("name");
            $table->dropColumn("surname");
            $table->dropColumn("patronymic");
            $table->string("fullname")->after("telegram_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string("name");
            $table->string("surname");
            $table->string("patronymic");

            $table->dropColumn("fullname");
        });
    }
};

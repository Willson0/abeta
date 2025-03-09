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
        Schema::table('admins', function (Blueprint $table) {
            $table->string("login")->after("telegram_id")->nullable();
            $table->string("password")->after("login")->nullable();
            $table->dateTime("entry_date")->after("password")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn("login");
            $table->dropColumn("password");
            $table->dropColumn("entry_date");
        });
    }
};

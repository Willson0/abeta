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
        Schema::table('user_webinars', function (Blueprint $table) {
            $table->boolean("added_calendar")->default(false)->after("data");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_webinars', function (Blueprint $table) {
            $table->dropColumn("added_calendar");
        });
    }
};

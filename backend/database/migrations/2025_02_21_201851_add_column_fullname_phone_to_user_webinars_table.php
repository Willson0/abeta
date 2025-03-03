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
            $table->string("fullname")->after("webinar_id");
            $table->string("phone")->after("webinar_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_webinars', function (Blueprint $table) {
            $table->dropColumn("fullname");
            $table->dropColumn("phone");
        });
    }
};

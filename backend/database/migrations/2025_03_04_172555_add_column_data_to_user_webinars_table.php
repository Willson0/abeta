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
            $table->json("data")->after('webinar_id')->nullable();
            $table->dropColumn("phone", "fullname");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_webinars', function (Blueprint $table) {
            $table->dropColumn("data");
            $table->string("phone");
            $table->string("fullname");
        });
    }
};

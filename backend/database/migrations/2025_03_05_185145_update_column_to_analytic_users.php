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
        Schema::table('analytic_users', function (Blueprint $table) {
            $table->dropColumn("phone", "fullname", "investment_portfolio");
            $table->json("data")->after('analytic_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytic_users', function (Blueprint $table) {
            $table->string("phone");
            $table->string("fullname");
            $table->string("investment_portfolio");
            $table->dropColumn("data");
        });
    }
};

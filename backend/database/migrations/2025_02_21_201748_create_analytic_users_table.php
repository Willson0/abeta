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
        Schema::create('analytic_users', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("analytic_id");

            $table->index("user_id");
            $table->index("analytic_id");

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('analytic_id')->references('id')->on('analytics')->onDelete('cascade');

            $table->string("phone");
            $table->string("fullname");
            $table->string("investment_portfolio");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytic_users');
    }
};

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
        Schema::create('supports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger("user_id");
            $table->index('user_id');
            $table->foreign("user_id")->references("id")->on("users")->onDelete("cascade");

            $table->text("text");

            $table->unsignedBigInteger("admin_id")->nullable();
            $table->index('admin_id');
            $table->foreign("admin_id")->references("id")->on("admins")->onDelete("cascade");

            $table->boolean("closed")->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supports');
    }
};

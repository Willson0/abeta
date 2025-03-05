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
            $table->text("calendly_access_token")->after("expert_mailing")->nullable();
            $table->text("calendly_refresh_token")->after("calendly_access_token")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("calendly_access_token");
            $table->dropColumn("calendly_refresh_token");
        });
    }
};

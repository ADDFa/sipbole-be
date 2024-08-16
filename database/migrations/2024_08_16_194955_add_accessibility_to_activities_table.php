<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("activities", function (Blueprint $table) {
            $table->enum("accesibility", ["user", "admin"])->default("user");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("activities", function (Blueprint $table) {
            $table->removeColumn("accesibility");
        });
    }
};

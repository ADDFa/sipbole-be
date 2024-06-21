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
        Schema::create("warrants_boats", function (Blueprint $table) {
            $table->id();
            $table->foreignId("warrant_id")->constrained("warrants");
            $table->foreignId("boat_id")->constrained("boats");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("warrants_boats");
    }
};

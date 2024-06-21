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
        Schema::create("warrants", function (Blueprint $table) {
            $table->id();
            $table->enum("type", ["Harkamtibnas", "Kegiatan Unggulan"]);
            $table->string("letter");
            $table->integer("number_of_personel");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("warrants");
    }
};

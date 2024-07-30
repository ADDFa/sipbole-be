<?php

use App\Models\Report;
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
        Schema::create("reports", function (Blueprint $table) {
            $table->id();
            $table->foreignId("warrant_id")->constrained("warrants");
            $table->foreignId("boat_id")->constrained("boats");
            $table->enum("type", ["Harkamtibmas", "Kegiatan Unggulan"]);
            $table->string("year");
            $table->enum("month", Report::months());
            $table->string("report");
            $table->string("execution_warrant");
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
        Schema::dropIfExists("reports");
    }
};

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
        Schema::create("activity_reports", function (Blueprint $table) {
            $table->id();
            $table->foreignId("activity_id")->constrained("activities")->cascadeOnDelete();
            $table->foreignId("report_id")->constrained("reports")->cascadeOnDelete();
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
        Schema::dropIfExists("activity_reports");
    }
};

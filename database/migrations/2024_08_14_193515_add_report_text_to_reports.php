<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table("reports", function (Blueprint $table) {
            $table->text("report_text")->nullable()->after("report");
        });

        // update report column
        DB::statement("ALTER TABLE reports MODIFY COLUMN report varchar(255) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("reports", function (Blueprint $table) {
            $table->dropColumn("report_text");
        });

        // update report column
        DB::statement("ALTER TABLE reports MODIFY COLUMN report varchar(255) NOT NULL");
    }
};

<?php

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChangeBoatIdValue extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // setiap boat_id di table laporan yang tidak memiliki warrant_id (sprint utama), 
        // maka itu adalah laporan sar, dan laporan sar diisi oleh admin,
        // sehingga tidak terdapat boat_id (id kapal) yang terdaftar
        // maka dari itu, seluruh data laporan yang tidak memiliki warrant_id tetapi memiliki
        // boat_id, maka boat_id diubah menjadi null

        $sarReports = Report::where("warrant_id", null)->get();
        foreach ($sarReports as $sarReport) {
            $sarReport->boat_id = null;
            $sarReport->save();
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Boat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ["number"    => "KP XXVII - 2001"],
            ["number"    => "KP XXVII - 2002"],
            ["number"    => "KP XXVII - 2004"],
            ["number"    => "KP XXVII - 2005"],
            ["number"    => "KP XXVII - 2007"],
            ["number"    => "KP XXVII - 2008"],
            ["number"    => "KP XXVII - 2009"],
            ["number"    => "KP XXVII - 2010"]
        ];

        foreach ($data as $value) {
            $boat = new Boat($value);
            $boat->save();
        }
    }
}

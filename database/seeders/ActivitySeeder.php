<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ["activity" => "PATROLI PERAIRAN"],
            ["activity" => "RIKSA KAPAL"],
            ["activity" => "BINMAS PERAIRAN"],
            ["activity" => "SAR/LAKA AIR"]
        ];

        foreach ($data as $value) {
            $activity = new Activity($value);
            $activity->save();
        }
    }
}

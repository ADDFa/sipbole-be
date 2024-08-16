<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccesibilityInActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activities = Activity::all();

        foreach ($activities as $activity) {
            $activity->accesibility = $activity->activity === "SAR/LAKA AIR" ? "admin" : "user";
            $activity->save();
        }
    }
}

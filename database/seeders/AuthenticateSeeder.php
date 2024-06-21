<?php

namespace Database\Seeders;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuthenticateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                "credential"    => [
                    "username"  =>  "admin-sipbole",
                    "password"  =>  password_hash("12345678", PASSWORD_DEFAULT)
                ],
                "user"          => [
                    "name"      => "Komandan Tertinggi",
                    "grade"     => "KT"
                ]
            ]
        ];

        foreach ($data as $value) {
            $auth = new Credential($value["credential"]);
            $auth->save();

            $user = new User($value["user"]);
            $user->boat_id = 1;
            $user->credential_id = 1;
            $user->save();
        }
    }
}

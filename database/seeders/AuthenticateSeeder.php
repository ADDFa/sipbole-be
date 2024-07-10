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
                    "username"  =>  "admin-si_pal",
                    "password"  =>  password_hash("password", PASSWORD_DEFAULT),
                    "role"      => "admin"
                ],
                "user"          => [
                    "name"      => "Admin",
                    "grade"     => "KT",
                    "boat_id"   => 1
                ]
            ]
        ];

        foreach ($data as $value) {
            $auth = new Credential($value["credential"]);
            $auth->save();

            $user = new User($value["user"]);
            $user->credential_id = 1;
            $user->save();
        }
    }
}

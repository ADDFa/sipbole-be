<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Boat;
use App\Models\User;

class InfoController extends Controller
{
    public function index()
    {
        $users = User::whereHas("credential", function ($query) {
            $query->where("role", "user");
        })->get()->count();
        $boats = Boat::all()->count();

        return Response::result([
            "users" => $users,
            "boats" => $boats
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = User::with(["boat"])->get();
        return Response::result($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "boat_id"   => "required|exists:boats,id",
            "username"  => "required|unique:credentials",
            "name"      => "required|string",
            "grade"     => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $credential = new Credential($validator->safe(["username"]));
        $credential->password = password_hash("12345678", PASSWORD_DEFAULT);
        $credential->save();

        $user = new User($validator->safe([
            "boat_id",
            "name",
            "grade"
        ]));
        $user->credential_id = $credential->id;
        $user->save();

        return Response::result([
            "credential"    => $credential,
            "user"          => $user
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        $result = User::with(["credential", "boat"])->find($user->id);
        return Response::result($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            "boat_id"   => "required|exists:boats,id",
            "name"      => "required|string",
            "grade"     => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $user->update($validator->validate());
        $user->save();

        return Response::result($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $credential = Credential::find($user->credential_id);
        $credential->delete();

        return Response::result($user);
    }
}

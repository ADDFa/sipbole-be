<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = User::with(["boat"])->whereHas("credential", function ($query) {
            $query->where("role", "user");
        })->get();
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
    public function show(Request $request, User $user)
    {
        if ($request->get("role") === "user") {
            if ($request->get("user") != $user->id) {
                return Response::errors("Anda tidak memiliki akses ke konten ini!", 401);
            }
        }

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

    public function updateProfile(Request $request, User $user)
    {
        $credential = Credential::find($user->credential_id);

        $validator = Validator::make($request->all(), [
            "name"      => "required|string",
            "username"  => [
                "required",
                Rule::unique("credentials")->ignore($credential->id)
            ]
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $user->name = $request->name;
        $credential->username = $request->username;

        $user->save();
        $credential->save();

        return AuthController::generateToken($credential);
    }

    public function updateProfilePic(Request $request, User $user)
    {
        $photo = $request->file("profile_picture");
        if ($photo) {
            $old = $user->profile_picture;
            if ($old) {
                $fileName = explode("/", $old);
                $fileName = end($fileName);
                Storage::delete("public/users/{$fileName}");
            }

            $photo = $photo->store("public/users");
            $user->profile_picture = Storage::url($photo);
            $user->save();
        }

        return Response::result($user);
    }
}

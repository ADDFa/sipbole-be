<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Credential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CredentialController extends Controller
{
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "old_password"      => "required|string",
            "new_password"      => "required|string",
            "confirm_password"  => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $authId = $request->get("auth_id");
        $credential = Credential::find($authId);

        // verify
        $verify = password_verify($request->old_password, $credential->password);
        if (!$verify) return Response::message("Password Salah!", 401);

        // equals
        if ($request->new_password !== $request->confirm_password) {
            return Response::message("Password tidak sama!");
        }

        $updatePassword = password_hash($request->new_password, PASSWORD_DEFAULT);
        $credential->password = $updatePassword;
        $credential->save();

        return Response::result($credential);
    }
}

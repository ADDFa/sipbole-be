<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Boat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BoatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = Boat::all();
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
            "number"        => "required|unique:boats,number",
            "information"   => "string|nullable",
            "picture"       => [
                "required",
                Rule::imageFile()->max(2048)
            ]
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $picture = $request->file("picture")->store("public/boats");

        $boat = new Boat($validator->safe(["number", "information"]));
        $boat->picture = Storage::url($picture);
        $boat->save();

        return Response::result($boat);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Boat  $boat
     * @return \Illuminate\Http\Response
     */
    public function show(Boat $boat)
    {
        $result = Boat::with("users")->find($boat->id);
        return Response::result($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Boat  $boat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Boat $boat)
    {
        $validator = Validator::make($request->all(), [
            "number"    => [
                "required",
                Rule::unique("boats")->ignore($boat->id)
            ],
            "information" => "string|nullable",
            "picture"   => Rule::imageFile()->max(2048)
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $picture = $request->file("picture");
        if ($picture) {
            $this->removePicture($boat->picture);
            $boat->picture = Storage::url($picture->store("public/boats"));
        }

        $boat->update($validator->safe(["number", "information"]));
        $boat->save();

        return Response::result($boat);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Boat  $boat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Boat $boat)
    {
        $boat->delete();
        return Response::result($boat);
    }

    private function removePicture($fileName)
    {
        if ($fileName) {
            $fileName = explode("/", $fileName);
            $fileName = end($fileName);

            Storage::delete("public/boats/{$fileName}");
        }
    }
}

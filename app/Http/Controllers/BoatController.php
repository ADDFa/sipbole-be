<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Boat;
use Illuminate\Http\Request;
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
            "information"   => "string|nullable"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $boat = new Boat($validator->validate());
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
        return Response::result($boat);
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
            "information" => "string|nullable"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $boat->update($validator->validate());
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
}

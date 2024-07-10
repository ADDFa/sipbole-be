<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\WarrantsBoat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WarrantsBoatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // 
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WarrantsBoat  $warrantsBoat
     * @return \Illuminate\Http\Response
     */
    public function show(WarrantsBoat $warrantsBoat)
    {
        //
    }

    public function read(WarrantsBoat $warrantsBoat)
    {
        $warrantsBoat->read = true;
        $warrantsBoat->save();

        return Response::result($warrantsBoat);
    }
}

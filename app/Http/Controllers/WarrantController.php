<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\User;
use App\Models\Warrant;
use App\Models\WarrantsBoat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WarrantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $role = $request->get("role");
        $result = Warrant::orderBy("created_at", "desc");

        if ($role !== "admin") {
            $user = User::find($request->get("user"));
            $result = $result->whereHas("warrantsBoats", function ($query) use ($user) {
                $query->where("boat_id", $user->boat_id);
            })->with("warrantBoat");
        }

        return Response::result($result->get());
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
            "type"      => [
                "required",
                Rule::in(["Harkamtibmas", "Kegiatan Unggulan"])
            ],
            "letter"    => [
                "required",
                Rule::file()->types("application/pdf")->max(5120)
            ],
            "number_of_personnel"   => "required|integer|min:1",
            "boats"     => "required|array",
            "boats.*"   => "required|exists:boats,id"
        ]);
        if ($validator->fails()) return Response::result($validator->errors());

        return DB::transaction(function () use ($request, $validator) {
            try {
                $letterPdf = $request->file("letter");
                $letter = $letterPdf->store("public/letters");
                if (!$letter) return Response::message("Gagal mengupload file!");

                $warrant = new Warrant($validator->safe(["type", "number_of_personnel"]));
                $warrant->letter = Storage::url($letter);
                $warrant->letter_file_name = $letterPdf->getClientOriginalName();
                $warrant->save();

                $boats = $request->boats;
                foreach ($boats as $boat) {
                    $warrantBoats = new WarrantsBoat([
                        "warrant_id"    => $warrant->id,
                        "boat_id"       => $boat
                    ]);
                    $warrantBoats->save();
                }

                DB::commit();
                return Response::result($warrant);
            } catch (\Exception $e) {
                DB::rollBack();
                return Response::message($e->getMessage());
            }
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Warrant  $warrant
     * @return \Illuminate\Http\Response
     */
    public function show(Warrant $warrant)
    {
        return Response::result($warrant->load("warrantsBoats.boat"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Warrant  $warrant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Warrant $warrant)
    {
        $validator = Validator::make($request->all(), [
            "type" => [
                "required",
                Rule::in(["Harkamtibmas", "Kegiatan Unggulan"])
            ],
            "letter"    => Rule::file()->types("application/pdf")->max(5120),
            "number_of_personnel"   => "required|integer|min:1",
            "boats"     => "required|array",
            "boats.*"   => "required|exists:boats,id"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        DB::transaction(function () use ($warrant, $validator, $request) {
            try {
                $warrant->update($validator->safe(["type", "number_of_personnel"]));

                $letter = $request->file("letter");
                if ($letter) {
                    // upload new file
                    $name = $letter->getClientOriginalName();
                    $letter = $letter->store("public/letters");
                    if (!$letter) return Response::message("Gagal mengupload file!");

                    // remove old file
                    $fileName = explode("/", $warrant->letter);
                    $fileName = end($fileName);
                    Storage::delete("public/letters/{$fileName}");

                    $warrant->letter = Storage::url($letter);
                    $warrant->letter_file_name = $name;
                    $warrant->save();
                }

                WarrantsBoat::where("warrant_id", $warrant->id)->get()->each->delete();
                foreach ($request->boats as $boat) {
                    $warrantBoat = new WarrantsBoat([
                        "warrant_id"    => $warrant->id,
                        "boat_id"       => $boat
                    ]);
                    $warrantBoat->save();
                }

                DB::commit();
                return Response::result($warrant);
            } catch (\Exception $e) {
                DB::rollBack();
                return Response::message("Maaf, terjadi kesalahan!", 500);
            }
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Warrant  $warrant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Warrant $warrant)
    {
        $warrant->delete();
        return Response::result($warrant);
    }
}

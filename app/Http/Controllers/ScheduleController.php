<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->all()) return Response::result(Schedule::all());

        if ($request->year) {
            $schedules = Schedule::where("year", $request->year);
        }

        if ($request->month) {
            $schedules->where("month", $request->month);
        }

        if ($request->date) {
            $schedules->where("date", $request->date);
        }

        return Response::result($schedules->get());
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
            "date"          => "required|date_format:Y-m-d",
            "description"   => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $dates = explode("-", $request->date);
        $existsSchedule = Schedule::where("year", $dates[0])
            ->where("month", $dates[1])
            ->where("date", $dates[2])
            ->first();
        if ($existsSchedule) {
            return Response::message("Tanggal {$dates[2]} sudah terisi!");
        }

        $schedule = new Schedule($validator->safe(["description"]));
        $schedule->year = $dates[0];
        $schedule->month = $dates[1];
        $schedule->date = $dates[2];
        $schedule->save();

        return Response::result($schedule);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function show(Schedule $schedule)
    {
        return Response::result($schedule);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Schedule $schedule)
    {
        $validator = Validator::make($request->all(), [
            "date"          => "required|date_format:Y-m-d",
            "description"   => "required|string"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $dates = explode("-", $request->date);
        $existsSchedule = Schedule::where("year", $dates[0])
            ->where("month", $dates[1])
            ->where("date", $dates[2])
            ->first();
        if ($existsSchedule && $existsSchedule->id !== $schedule->id) {
            return Response::message("Tanggal {$dates[2]} sudah terisi!");
        }

        $schedule->year = $dates[0];
        $schedule->month = $dates[1];
        $schedule->date = $dates[2];
        $schedule->description = $request->description;
        $schedule->save();

        return Response::result($schedule);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return Response::result($schedule);
    }
}

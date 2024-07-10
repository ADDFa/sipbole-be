<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\ActivityReport;
use Illuminate\Http\Request;

class ActivityReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $result = ActivityReport::with(["activity", "report"]);

        if ($request->year) {
            $result->whereHas("report", function ($query) use ($request) {
                $query->where("year", $request->year);
            });
        }

        if ($request->type) {
            $result->whereHas("report", function ($query) use ($request) {
                $query->where("type", $request->type);
            });
        }

        return Response::result($result->get());
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ActivityReport  $activityReport
     * @return \Illuminate\Http\Response
     */
    public function show(ActivityReport $activityReport)
    {
        //
    }
}

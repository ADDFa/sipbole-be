<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\ActivityReport;
use App\Models\Report;
use App\Models\Warrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "warrant_id"        => "required|exists:warrants,id",
            "boat_id"           => "required|exists:boats,id",
            "report"            => "required|string",
            "activities"        => "required|array",
            "activities.*"      => "required|exists:activities,id"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        return DB::transaction(function () use ($request, $validator) {
            try {
                $warrant = Warrant::find($request->warrant_id);
                $months = Report::months();
                $monthNum = date("n");
                $monthIndex = $monthNum - 1;

                $data = $validator->safe(["warrant_id", "boat_id", "report"]);
                $report = new Report($data);
                $report->type = $warrant->type;
                $report->year = date("Y");
                $report->month = $months[$monthIndex];
                $report->save();

                foreach ($request->activities as $activityId) {
                    $activityReport = new ActivityReport([
                        "report_id"     => $report->id,
                        "activity_id"   => $activityId
                    ]);
                    $activityReport->save();
                }

                DB::commit();
                return Response::result($report);
            } catch (\Exception $e) {
                DB::rollBack();
                return Response::message("Server Error!", 500);
            }
        });
    }
}

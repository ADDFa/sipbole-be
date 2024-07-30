<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\ActivityReport;
use App\Models\Report;
use App\Models\Warrant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            "report"            => [
                "required",
                Rule::file()->types("pdf")->max(5120)
            ],
            "execution_warrant" => [
                "required",
                Rule::file()->types("pdf")->max(5120)
            ],
            "reported_date"     => "required|date_format:Y-m-d",
            "activities"        => "required|array",
            "activities.*"      => "required|exists:activities,id"
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        return DB::transaction(function () use ($request, $validator) {
            try {
                $data = $validator->safe(["warrant_id", "boat_id"]);
                $failStoringFileMessage = Response::message("Gagal mengupload file!");
                $warrant = Warrant::find($request->warrant_id);

                $executionWarrantPdf = $request->file("execution_warrant");
                $executionWarrant = $executionWarrantPdf->store("public/letters");
                if (!$executionWarrant) return $failStoringFileMessage;

                $reportPdf = $request->file("report");
                $reportPath = $reportPdf->store("public/reports");
                if (!$reportPath) return $failStoringFileMessage;

                $reportedDate = $request->reported_date;
                $reportedDates = explode("-", $reportedDate);
                $year = $reportedDates[0];
                $month = (int)$reportedDates[1];

                $report = new Report($data);
                $report->type = $warrant->type;
                $report->year = $year;
                $report->month = $month;
                $report->report = Storage::url($reportPath);
                $report->execution_warrant = Storage::url($executionWarrant);
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

    public function destroy()
    {
        $reports = Report::all();
        $statuses = [];

        foreach ($reports as $report) {
            $files = explode("/", $report->execution_warrant);
            $fileName = end($files);

            $reportPath = "/letters/$fileName";
            $deleted = Storage::disk("public")->delete($reportPath);
            $status = [
                "deleted"   => $deleted,
                "report"    => $report
            ];
            array_push($statuses, $status);
        }

        return $statuses;
    }
}

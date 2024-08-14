<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Models\ActivityReport;
use App\Models\Credential;
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
        $validator = Validator::make($request->all(), [
            "year"  => "string",
            "month" => Rule::in(Report::months())
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $credential = Credential::with("user")->find($request->get("auth_id"));
        $reports = [];

        if ($credential->role === "admin") {
            $reports = Report::orderBy("created_at", "desc");
        }

        if ($credential->role === "user") {
            $reports = Report::where("boat_id", $credential->user->boat_id)
                ->orderBy("date", "asc");
        }

        if ($reports) {
            if ($request->year) {
                $reports = $reports->where("year", $request->year);
            }

            if ($request->month) {
                $reports = $reports->where("month", $request->month);
            }

            $reports = $reports->with("warrant")->get();
        }

        return Response::result($reports);
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
                Rule::file()->types("pdf")->max(5120)
            ],
            "report_text"       => "string|nullable",
            "execution_warrant" => [
                "required",
                Rule::file()->types("pdf")->max(5120)
            ],
            "reported_date"     => "required|date_format:Y-m-d",
            "activities"        => "required|array",
            "activities.*"      => "required|exists:activities,id",
            "category"          => [
                "required",
                Rule::in(["file", "text"])
            ]
        ]);

        // cek laporan, tidak boleh kosong
        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($request) {
            if (!$request->report && !$request->report_text) {
                $validator->errors()->add("report", "Laporan harus diisi!");
                $validator->errors()->add("report_text", "Laporan harus diisi!");
            }
        });
        if ($validator->fails()) return Response::errors($validator->errors());

        return DB::transaction(function () use ($request, $validator) {
            try {
                $data = $validator->safe(["warrant_id", "boat_id", "category"]);
                $failStoringFileMessage = Response::message("Gagal mengupload file!");
                $warrant = Warrant::find($request->warrant_id);

                $executionWarrantPdf = $request->file("execution_warrant");
                $executionWarrant = $executionWarrantPdf->store("public/letters");
                if (!$executionWarrant) return $failStoringFileMessage;

                $reportedDate = $request->reported_date;
                $reportedDates = explode("-", $reportedDate);
                $year = $reportedDates[0];
                $month = (int)$reportedDates[1];
                $date = $reportedDates[2];

                $report = new Report($data);
                $report->type = $warrant->type;
                $report->year = $year;
                $report->month = $month;
                $report->date = $date;
                if ($request->category === "file") {
                    $reportPdf = $request->file("report");
                    $reportPath = $reportPdf->store("public/reports");
                    if (!$reportPath) return $failStoringFileMessage;
                    $report->report = Storage::url($reportPath);
                }
                if ($request->category === "text") {
                    $report->report_text = $request->report_text;
                }
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
                return Response::message("Server Error!\n {$e->getMessage()}", 500);
            }
        });
    }
}

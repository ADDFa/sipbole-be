<?php

namespace App\Http\Controllers;

use App\Http\Response;
use App\Services\Fonnte;
use App\Models\Activity;
use App\Models\ActivityReport;
use App\Models\Credential;
use App\Models\Report;
use App\Models\SarDocumentation;
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
            "year"      => "string",
            "month"     => Rule::in(Report::months()),
            "category"  => Rule::in(["sar"])
        ]);
        if ($validator->fails()) return Response::errors($validator->errors());

        $credential = Credential::with("user")->find($request->get("auth_id"));
        $reports = [];

        if ($credential->role === "admin") {
            $reports = Report::orderBy("date", "desc");
        }

        if ($credential->role === "user") {
            $reports = Report::where("boat_id", $credential->user->boat_id)
                ->orderBy("date", "asc");
        }

        if ($reports) {
            if ($credential->role === "admin" && $request->category === "sar") {
                $reports = $reports->where("boat_id", null)->where("warrant_id", null);
            }

            if ($request->year) {
                $reports = $reports->where("year", $request->year);
            }

            if ($request->month) {
                $reports = $reports->where("month", $request->month);
            }

            $reports = $reports->with(["warrant", "sarDocumentations"])->get();
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
            "activities.*"      => "required|exists:activities,id"
        ]);

        // cek laporan, tidak boleh kosong
        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($request) {
            if (!$request->hasFile("report") && !$request->report_text) {
                $validator->errors()->add("report", "Laporan harus diisi!");
                $validator->errors()->add("report_text", "Laporan harus diisi!");
            }
        });
        if ($validator->fails()) return Response::errors($validator->errors());

        return DB::transaction(function () use ($request, $validator) {
            try {
                $data = $validator->safe(["warrant_id", "boat_id"]);
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
                if ($request->hasFile("report")) {
                    $reportPdf = $request->file("report");
                    $reportPath = $reportPdf->store("public/reports");
                    if (!$reportPath) return $failStoringFileMessage;

                    $report->category = "file";
                    $report->report = Storage::url($reportPath);
                } else {
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

                $notification = new Fonnte("ntH-+Pc@F@fGvHi8Wcf1");
                $notification->sendMessage("082374632340", "Tes");

                DB::commit();
                return Response::result($report);
            } catch (\Exception $e) {
                DB::rollBack();
                return Response::message("Server Error!\n {$e->getMessage()}", 500);
            }
        });
    }

    public function storeSar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "type"      => [
                "required",
                Rule::in(["Harkamtibmas", "Kegiatan Unggulan"])
            ],
            "reported_date" => "required|date_format:Y-m-d",
            "report"        => Rule::file()->types("pdf")->max(5120),
            "report_text"   => "string|nullable",
            "execution_warrant" => [
                "required",
                Rule::file()->types("pdf")->max(5120)
            ],
            "documentations"    => "required|array",
            "documentations.*"  => [
                "required",
                Rule::file()->types("image/*")->max(5120)
            ]
        ]);

        $validator->after(function (\Illuminate\Validation\Validator $validator) use ($request) {
            if (!$request->hasFile("report") && !$request->report_text) {
                $validator->errors()->add("report", "Laporan harus diisi!");
                $validator->errors()->add("report_text", "Laporan harus diisi!");
            }
        });
        if ($validator->fails()) return Response::errors($validator->errors());

        return DB::transaction(function () use ($validator, $request) {
            try {
                $activityId = Activity::where("activity", "SAR/LAKA AIR")->value("id");
                $data = $validator->safe(["type", "boat_id", "category"]);
                $failStoringFileMessage = Response::message("Gagal mengupload file!");

                if (!$activityId) return Response::message("Maaf, Aktifitas Sar/Laka Air sedang tidak tersedia!");

                $executionWarrantPdf = $request->file("execution_warrant");
                $executionWarrant = $executionWarrantPdf->store("public/letters");
                if (!$executionWarrant) return $failStoringFileMessage;

                $reportedDate = $request->reported_date;
                $reportedDates = explode("-", $reportedDate);
                $year = $reportedDates[0];
                $month = (int)$reportedDates[1];
                $date = $reportedDates[2];

                $report = new Report($data);
                $report->year = $year;
                $report->month = $month;
                $report->date = $date;
                if ($request->hasFile("report")) {
                    $reportPdf = $request->file("report");
                    $reportPath = $reportPdf->store("public/reports");
                    if (!$reportPath) return $failStoringFileMessage;

                    $report->category = "file";
                    $report->report = Storage::url($reportPath);
                } else {
                    $report->report_text = $request->report_text;
                }
                $report->execution_warrant = Storage::url($executionWarrant);
                $report->save();

                $activityReport = new ActivityReport([
                    "report_id"     => $report->id,
                    "activity_id"   => $activityId
                ]);
                $activityReport->save();

                $documentations = $request->file("documentations");
                foreach ($documentations as $documentation) {
                    $documentationPath = $documentation->store("public/sar-documentations");
                    $documentationFileName = $documentation->getClientOriginalName();
                    $documentationUrl = Storage::url($documentationPath);

                    $sarDocumentation = new SarDocumentation([
                        "report_id"     => $report->id,
                        "image_name"    => $documentationFileName,
                        "image_path"    => $documentationUrl
                    ]);
                    $sarDocumentation->save();
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

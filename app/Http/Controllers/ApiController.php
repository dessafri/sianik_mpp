<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Repositories\ReportRepository;
use App\Repositories\TokenRepository;

class ApiController extends Controller
{
    protected $reportRepository;
    protected $tokenRepository;
    public function __construct(ReportRepository $reportRepository,TokenRepository $tokenRepository)
    {
        $this->reportRepository = $reportRepository;
        $this->tokenRepository = $tokenRepository;
    }
    public function getAntrianList()
    {
        $antrianList = $this->reportRepository->getAntrianListReport();

        return response()->json(['antrian_list' => $antrianList], 200);
    }
    public function getPhoneQueueList()
    {
        $antrianList = $this->tokenRepository->getPhoneQueueList();

        return response()->json(['antrian_list' => $antrianList], 200);
    }
    public function getReportNumberList(Request $request)
    {
        $report = $this->reportRepository->getReportNumbers($request);

        return response()->json(['data' => $report], 200);
    }
}

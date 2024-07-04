<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\ReportRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\CallRepository;
use App\Models\OperationalTime;

class DisplayController extends Controller
{
    public $services,$callRepository;

    public function __construct(ServiceRepository $services,CallRepository $callRepository,ReportRepository $reportRepository)
    {
        $this->services = $services;
        $this->callRepository = $callRepository;
        $this->reportRepository = $reportRepository;
    }

    public function showDisplayUrl()
    {
        $date = now();
        $dayOfWeek = $date->format('l');

        $time = OperationalTime::where('day', $dayOfWeek)
        ->where('status', 'Offline')
        ->first();

        return view('display.index', 
        ['services' => $this->services->getAllActiveServicesWithLimits(), 
        'calls' => $this->callRepository->getCallsForAntrian(), 
        'date' => Carbon::now()->toDateString(), 
        'settings' => Setting::first(),
        'time' => $time,
        'datatokens'=> $this->callRepository->getCallsForDisplay2(),
        'file'=>'storage/app/public/tokens_for_display.json']);
    }

    public function showDisplayServicesUrl()
    {
        $date = now();
        $dayOfWeek = $date->format('l');

        $time = OperationalTime::where('day', $dayOfWeek)
        ->where('status', 'Offline')
        ->first();

        return view('display.services', 
        ['services' => $this->services->getAllActiveServices(), 
        'calls' => $this->callRepository->getCallsForAntrian(), 
        'date' => Carbon::now()->toDateString(), 
        'settings' => Setting::first(),
        'time' => $time,
        'file'=>'storage/app/public/tokens_for_display.json']);
    }

    public function showDisplayOnlineUrl()
    {
        return view('display.online', 
        [
        'calls' => $this->services->getCallsForAntrian(),
        'date' => Carbon::now()->toDateString(), 
        'settings' => Setting::first(),
        'file'=>'storage/app/public/tokens_for_display-online.json']);
    }

    public function showDisplayOnlineServiceUrl()
    {
        return view('display.online-service', 
        ['services' => $this->services->getAllActiveServicesWithLimits(),
        'calls' => $this->services->getCallsForAntrian(),
        'date' => Carbon::now()->toDateString(), 
        'settings' => Setting::first(),
        'file'=>'storage/app/public/tokens_for_display-online.json']);
    }
}
<?php

namespace App\Repositories;

use App\Consts\CallStatuses;
use App\Models\Call;
use App\Models\Queue;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ReportRepository
{
    public function getUserReport($user_id, $date)
    {
        $query = DB::table('calls_report')->join('services', 'calls_report.service_id', '=', 'services.id')
            ->join('counters', 'calls_report.counter_id', '=', 'counters.id')
            ->where('calls_report.called_date', '=', $date);
        if (isset($user_id)) $query = $query->where('calls_report.user_id', '=', $user_id);
        $query = $query->select('calls_report.id', 'calls_report.token_number', 'calls_report.token_letter', 'counters.name as counter_name', 'services.name as service_name', 'calls_report.call_status_id');
        $report = $query->get();
        return $report;
    }
    
    public function getSeesionList()
    {
        return DB::table('sessions')
        ->join('services', 'sessions.service_id', '=', 'services.id')
        ->where('counter_id', '!=', NULL)
        ->get();
    }
    
    public function getQueueListReport($starting_date, $ending_date)
    {
        $report = DB::table('queues_report')->Leftjoin('calls_report', 'calls_report.queue_id', '=', 'queues_report.id')
            ->join('services', 'services.id', '=', 'queues_report.service_id')
            ->Leftjoin('counters', 'counters.id', '=', 'calls_report.counter_id')
            ->Leftjoin('users', 'users.id', '=', 'calls_report.user_id')
            ->where('queues_report.created_at', '>=', Carbon::parse($starting_date)->startOfDay())->where('queues_report.created_at', '<', Carbon::parse($ending_date)->endOfDay())
            ->select('services.name as service_name', 'queues_report.id', 'queues_report.status_queue', 'queues_report.name', 'queues_report.nik', 'queues_report.phone', 'queues_report.created_at as date', 'queues_report.number as token_number', 'queues_report.letter as token_letter', 'queues_report.called', 'users.name as user_name', 'counters.name as counter_name')
            ->get();
        return $report;
    }

    public function getAntrianListView($data)
    {
        $today = $data;
        $tomorrow = date('Y-m-d', strtotime('+1 day', strtotime($today)));
        return DB::table('queues_report')
            ->leftJoin('calls_report', 'calls_report.queue_id', '=', 'queues_report.id')
            ->join('services', 'services.id', '=', 'queues_report.service_id')
            ->leftJoin('counters', 'counters.id', '=', 'calls_report.counter_id')
            ->leftJoin('users', 'users.id', '=', 'calls_report.user_id')
            ->where('queues_report.created_at', '>=', $today)
            ->where('queues_report.created_at', '<', $tomorrow)
            ->select(
                'services.name AS service_name',
                DB::raw('SUM(queues_report.called = 0) AS belum_dipanggil'),
                DB::raw('SUM(queues_report.called = 1) AS terpanggil'),
                DB::raw('SUM(calls_report.call_status_id = 2 ) AS tidak_hadir'),
                DB::raw('SUM(calls_report.call_status_id = 1 ) AS dilayani'),
                DB::raw('COUNT(queues_report.id) AS total_antrian'),
                DB::raw('MAX(CASE WHEN queues_report.called = 1 THEN queues_report.letter ELSE NULL END) AS letter_called'),
                DB::raw('MAX(CASE WHEN queues_report.called = 1 THEN queues_report.number ELSE NULL END) AS number_called')
            )
            ->groupBy('services.name')
        ->get();
    }

    public function getAntrianListReport()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        return DB::table('queues')
            ->leftJoin('calls', 'calls.queue_id', '=', 'queues.id')
            ->join('services', 'services.id', '=', 'queues.service_id')
            ->leftJoin('counters', 'counters.id', '=', 'calls.counter_id')
            ->leftJoin('users', 'users.id', '=', 'calls.user_id')
            ->where('queues.created_at', '>=', $today)
            ->where('queues.created_at', '<', $tomorrow)
            ->select(
                'services.name AS service_name',
                DB::raw('SUM(queues.called = 0) AS belum_dipanggil'),
                DB::raw('SUM(queues.called = 1) AS terpanggil'),
                DB::raw('SUM(calls.call_status_id = 2 ) AS tidak_hadir'),
                DB::raw('SUM(calls.call_status_id = 1 ) AS dilayani'),
                DB::raw('COUNT(queues.id) AS total_antrian'),
                DB::raw('MAX(CASE WHEN queues.called = 1 THEN queues.letter ELSE NULL END) AS letter_called'),
                DB::raw('MAX(CASE WHEN queues.called = 1 THEN queues.number ELSE NULL END) AS number_called')
            )
            ->groupBy('services.name')
        ->get();
    }

    public function getAntrianUserReport()
    {
        $id_login = Auth::id();
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
    
        return DB::table('calls')
            ->join('services', 'services.id', '=', 'calls.service_id')
            ->where('calls.created_at', '>=', $today)
            ->where('calls.created_at', '<', $tomorrow)
            ->where('calls.user_id', $id_login)
            ->select(
                'services.name AS service_name',
                DB::raw('SUM(calls.call_status_id = 2 ) AS tidak_hadir'),
                DB::raw('SUM(calls.call_status_id = 1 ) AS dilayani')
            )
            ->groupBy('services.name')
            ->get();
    }

    public function getAntrianUserListReport($date)
    {
        return DB::table('calls_report')
        ->select('users.id', 'users.name AS user_name', 
        DB::raw('COUNT(calls_report.id) AS total_antrian'),
        DB::raw('SUM(calls_report.call_status_id = 1) AS antrian_hadir'),
        DB::raw('SUM(calls_report.call_status_id = 2) AS tidak_hadir'),)
        ->join('users', 'users.id', '=', 'calls_report.user_id')
        ->whereDate('calls_report.created_at', $date)
        ->groupBy('users.id', 'users.name')
        ->get();
    }

    public function getMonthlyReport($data)
    {
        $query = DB::table('calls_report')
            ->join('counters', 'counters.id', '=', 'calls_report.counter_id')
            ->join('users', 'users.id', '=', 'calls_report.user_id')
            ->Leftjoin('call_statuses', 'calls_report.call_status_id', '=', 'call_statuses.id')
            ->Rightjoin('queues_report', 'calls_report.queue_id', '=', 'queues_report.id')
            ->join('services', 'services.id', '=', 'queues_report.service_id')
            ->where('queues_report.created_at', '>=', Carbon::parse($data->starting_date)->startOfDay())
            ->where('queues_report.created_at', '<', Carbon::parse($data->ending_date)->endOfDay());
        if (isset($data->service_id)) $query =  $query->where('queues_report.service_id', '=', $data->service_id);
        if (isset($data->counter_id)) $query =  $query->where('calls_report.counter_id', '=', $data->counter_id);
        if (isset($data->user_id)) $query =  $query->where('calls_report.user_id', '=', $data->user_id);
        if (isset($data->call_status)) $query =  $query->where('calls_report.call_status_id', '=', $data->call_status);
        $query = $query->select('users.name as user_name', 'queues_report.letter as token_letter', 'queues_report.number as token_number', 'queues_report.phone as queue_phone', 'queues_report.name as queue_name', 'queues_report.nik as queue_nik', 'queues_report.status_queue', 'services.name as service_name', 'counters.name as counter_name', 'queues_report.created_at as date', 'calls_report.started_at as called_at', 'calls_report.ended_at as served_at', 'calls_report.waiting_time as waiting_time', 'calls_report.served_time as served_time', 'calls_report.turn_around_time as total_time', 'call_statuses.name as status');
        $report = $query->orderBy('queues_report.created_at')->get();
        return $report;
    }

    public function getTodayYesterdayData()
    {
        $t_6 = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now()->startOfDay()->addHours(6))->count();
        $t_12 = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now()->startOfDay()->addHours(12))->count();
        $t_18 = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=',  Carbon::now()->startOfDay()->addHours(18))->count();
        $t_24 = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=',  Carbon::now()->startOfDay()->addHours(23)->addMinutes(59)->addSeconds(59))->count();

        $today_data = array(0, $t_6, $t_12, $t_18, $t_24);

        $y_6 = Queue::where('created_at', '>=', Carbon::yesterday()->startOfDay())->where('created_at', '<=', Carbon::yesterday()->startOfDay()->addHours(6))->count();
        $y_12 = Queue::where('created_at', '>=', Carbon::yesterday()->startOfDay())->where('created_at', '<=', Carbon::yesterday()->startOfDay()->addHours(12))->count();
        $y_18 = Queue::where('created_at', '>=', Carbon::yesterday()->startOfDay())->where('created_at', '<=',  Carbon::yesterday()->startOfDay()->addHours(18))->count();
        $y_24 = Queue::where('created_at', '>=', Carbon::yesterday()->startOfDay())->where('created_at', '<=',  Carbon::yesterday()->startOfDay()->addHours(23)->addMinutes(59)->addSeconds(59))->count();

        $yesterday_data = array(0, $y_6, $y_12, $y_18, $y_24);

        return ['today' => $today_data, 'yesterday' => $yesterday_data];
    }

    public function getTokenCounts($starting_date, $ending_date)
    {
        $queue = Queue::where('created_at', '>=', Carbon::parse($starting_date)->startOfDay())->where('created_at', '<', Carbon::parse($ending_date)->endOfDay())
            ->where('called', false)->count();
        $served = Call::where('created_at', '>=', Carbon::parse($starting_date)->startOfDay())->where('created_at', '<', Carbon::parse($ending_date)->endOfDay())
            ->where('call_status_id', CallStatuses::SERVED)->count();
        $noshow = Call::where('created_at', '>=', Carbon::parse($starting_date)->startOfDay())->where('created_at', '<', Carbon::parse($ending_date)->endOfDay())
            ->where('call_status_id', CallStatuses::NOSHOW)->count();
        $serving = Call::where('created_at', '>=', Carbon::parse($starting_date)->startOfDay())->where('created_at', '<', Carbon::parse($ending_date)->endOfDay())
            ->whereNull('call_status_id')->count();
        $counts = ['queue' => $queue, 'served' => $served, 'noshow' => $noshow, 'serving' => $serving];
        return $counts;
    }
    
    public function getReportNumbers($data)
    {
        return DB::table('queues_report')
        ->select('phone', DB::raw('COUNT(*) as total'))
        ->where('queues_report.created_at', '>=', Carbon::parse($data->starting_date)->startOfDay())
        ->where('queues_report.created_at', '<', Carbon::parse($data->ending_date)->endOfDay())
        ->where('queues_report.service_id', '=', $data->service_id)
        ->whereNotNull('phone')
        ->groupBy('phone')
        ->havingRaw('COUNT(*) > 2')
        ->orderByDesc('total')
        ->get();
    }
}

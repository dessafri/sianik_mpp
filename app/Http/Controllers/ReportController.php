<?php

namespace App\Http\Controllers;

use App\Models\CallStatus;
use App\Models\Counter;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Session;
use App\Models\BlockedNumber;
use App\Models\User;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exports\QueueExport;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\DB;
use App\Repositories\BlockedNumberRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportDataFromArray implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Nomor Antrian',
            'Nama',
            'Telepon',
            'NIK',
            'Tanggal',
            'Jenis Antrian',
        ];
    }
}

class ExportUserListFromArray implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Nama User',
            'Total Antrian',
            'Total Antrian Hadir',
            'Total Antrian Tidak Hadir',
        ];
    }
}

class ExportReportNumberFromArray implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Telepon',
            'Total',
        ];
    }
}

class ExportUserReportFromArray implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Layanan',
            'Nomor Antrian',
            'Loket',
            'Status',
        ];
    }
}

class ExportMonthlyReportFromArray implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Pengguna',
            'Nomor Antrian',
            'Layanan',
            'Nama',
            'NIK',
            'Telepon',
            'Status Antrian',
            'Loket',
            'Tanggal',
            'Disebut Pada',
            'Dilayanani Pada',
            'Waktu Menunggu',
            'Waktu Penyajian',
            'Total Waktu',
            'Status',
        ];
    }
}

class ReportController extends Controller
{
    protected $reportRepository;
    public $blocked_number;
    public function __construct(ReportRepository $reportRepository, BlockedNumberRepository $blocked_number)
    {
        $this->reportRepository = $reportRepository;
        $this->blocked_number = $blocked_number;
    }

    public function showUserReport(Request $request)
    {
        $report = null;
        $selected_user_id = null;
        $selected_date = null;
        if ($request->date) {
            $selected_user_id = $request->user_id;
            $selected_date = $request->date;
            $report = $this->reportRepository->getUserReport($request->user_id, $request->date);
        }
        return view('reports.user_report', ['users' => User::get(), 'reports' => $report, 'selected_user_id' => $selected_user_id, 'selected_date' => $selected_date]);
    }

    public function exportUserReport(Request $request)
    {
        $user_id = $request->input('user_id');
        $date = $request->input('date');

        $dateToday = Carbon::now()->format('d-m-Y H:i:s');
        $filename = "Export_Laporan_Pengguna_{$dateToday}.xlsx";

        $userCallData = DB::table('calls_report')
            ->where('user_id', $user_id)
            ->where('called_date', $date)
            ->get();

        $exportData = [];
        foreach ($userCallData as $data) {
            $token = $data->token_letter . '-' . $data->token_number;
            $status = ($data->call_status_id == 1) ? 'Hadir' : 'Tidak Hadir';
            $loket = DB::table('counters')->where('id', $data->counter_id)->value('name');
            $layanan = DB::table('services')->where('id', $data->service_id)->value('name');
            $exportData[] = [
                'Layanan' => $layanan,
                'Nomor Antrian' => $token,
                'Loket' => $loket,
                'Status' => $status,
            ];
        }

        $exportClass = new ExportUserReportFromArray($exportData);

        return Excel::download($exportClass, $filename);
    }

    public function showUserList(Request $request)
    {
        $report = null;
        $selected_date = null;
        if ($request->date) {
            $selected_date = $request->date;
            $report = $this->reportRepository->getAntrianUserListReport($request->date);
        }
        return view('reports.users_list_report', ['reports' => $report, 'selected_date' => $selected_date]);
    }

    public function exportUserList(Request $request)
    {
        $date = $request->input('date');

        $dateToday = Carbon::now()->format('d-m-Y H:i:s');
        $filename = "Export_Antrian_Pengguna_{$dateToday}.xlsx";


        $userListData = $this->reportRepository->getAntrianUserListReport($date);

        $exportData = [];
        foreach ($userListData as $data) {
            $exportData[] = [
                'Nama User' => $data->user_name,
                'Total Antrian' => $data->total_antrian,
                'Total Antrian Hadir' => $data->antrian_hadir,
                'Total Antrian Tidak Hadir' => $data->tidak_hadir,
            ];
        }

        $exportClass = new ExportUserListFromArray($exportData);

        return Excel::download($exportClass, $filename);
    }

    public function export(Request $request)
    {
        $startingDate = $request->input('starting_date');
        $endingDate = $request->input('ending_date');

        $dateToday = Carbon::now()->format('d-m-Y H:i:s');
        $filename = "Report_Antrian_{$dateToday}.xlsx";

        $queueData = DB::table('queues_report')
            ->whereBetween('created_at', [$startingDate, $endingDate])
            ->get();

        $exportData = [];
        foreach ($queueData as $data) {
            $token = $data->letter . '-' . $data->number;
            $exportData[] = [
                'Nomor Antrian' => $token,
                'Nama' => $data->name,
                'Telepon' => $data->phone,
                'NIK' => $data->nik,
                'Tanggal' => $data->created_at,
                'Jenis Antrian' => $data->status_queue,
            ];
        }

        $exportClass = new ExportDataFromArray($exportData);

        return Excel::download($exportClass, $filename);
    }

    public function add_block_number(Request $request)
    {

        $phoneNumber = $request->input('phone');

        $existingNumber = DB::table('blocked_numbers')->where('phone_number', $phoneNumber)->first();
        if ($existingNumber) {
            $request->session()->flash('error', 'Phone number already exists');
            return redirect()->route('report_number');
        }

        DB::beginTransaction();
        try {
            $blocked_number = $this->blocked_number->create(['phone_number' => $phoneNumber]);
            Storage::put('public/blocked_number_' . $blocked_number->id . '_display.json', json_encode([]));
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something Went Wrong');
            return redirect()->route('report_number');
        }
        DB::commit();

        $request->session()->flash('success', 'Succesfully inserted the record');
        return redirect()->route('report_number');
    }

    public function showQueueListReport(Request $request)
    {
        $report = null;
        $selected_starting_date = null;
        $selected_ending_date = null;
        if ($request->starting_date && $request->ending_date) {
            $selected_starting_date = $request->starting_date;
            $selected_ending_date = $request->ending_date;
            $report = $this->reportRepository->getQueueListReport($request->starting_date, $request->ending_date);
        }
        return view('reports.queue_list_report', ['reports' => $report, 'selected_starting_date' => $selected_starting_date, 'selected_ending_date' => $selected_ending_date, 'timezone' => Setting::first()->timezone]);
    }

    public function showMonitorAntrian(Request $request)
    {
        $monitors = null;
        $selected_date = null;

        if ($request->date) {
            $date = $request->date;
            $data = date('Y-m-d 00:00:00', strtotime($date));
            $selected_date = $request->date;
            $monitors = $this->reportRepository->getAntrianListView($data);
        } else {
            $selected_date = Carbon::now()->format('Y-m-d 00:00:00');
            $monitors = $this->reportRepository->getAntrianListView($selected_date);
        }
        return view(
            'reports.monitor_antrian',
            [
                'timezone' => Setting::first()->timezone,
                'monitors' => $monitors,
                'selected_date' => $selected_date
            ]
        );
    }

    public function getAntrianList(Request $request)
    {
        $antrianList = $this->reportRepository->getAntrianListReport();

        return response()->json(['antrian_list' => $antrianList], 200);
    }

    public function showResetSession()
    {
        $sessions = $this->reportRepository->getSeesionList();
        return view(
            'reports.reset_session',
            [
                'timezone' => Setting::first()->timezone,
                'sessions' => $sessions
            ]
        );
    }

    public function deleteSessions(Request $request)
    {
        try {
            DB::table('sessions')
                ->where('counter_id', $request->counter_id)
                ->update(['counter_id' => null, 'service_id' => null]);
        } catch (\Exception $e) {
            $request->session()->flash('error', 'Sometings Wrong');
        }

        $request->session()->flash('success', 'Successfully deleted the record');
        return redirect()->route('sessions_list');
    }

    public function showSatiticalReport()
    {
        $users = User::get();
        $services = Service::get();
        $counters = Counter::get();
        return view('reports.statitical_report', ['users' => $users, 'services' => $services, 'counters' => $counters]);
    }

    public function showMonthlyReport(Request $request)
    {
        $users = User::get();
        $services = Service::get();
        $counters = Counter::get();
        $statuses = CallStatus::get();
        $reports = null;
        $starting_date = null;
        $ending_date = null;
        $service = null;
        $counter = null;
        $user = null;
        $status = null;
        $count = null;
        if ($request->starting_date && $request->ending_date) {
            $starting_date = $request->starting_date;
            $ending_date = $request->ending_date;
            if (isset($request->service_id)) $service = $request->service_id;
            if (isset($request->counter_id)) $counter = $request->counter_id;
            if (isset($request->user_id)) $user = $request->user_id;
            if (isset($request->call_status)) $status = $request->call_status;

            $reports = $this->reportRepository->getMonthlyReport($request);
            $count = $this->reportRepository->getTokenCounts($request->starting_date, $request->ending_date);
        }


        return view('reports.monthly_report', ['token_count' => $count, 'users' => $users, 'services' => $services, 'counters' => $counters, 'statuses' => $statuses, 'reports' => $reports, 'timezone' => Setting::first()->timezone, 'selected' => ['starting_date' => $starting_date, 'ending_date' => $ending_date, 'counter' => $counter, 'service' => $service, 'user' => $user, 'status' => $status]]);
    }

    public function exportMonthlyReport(Request $request)
    {
        $dateToday = Carbon::now()->format('d-m-Y H:i:s');
        $filename = "Export_Laporan_Bulanan_{$dateToday}.xlsx";

        $monthlyReportData = $this->reportRepository->getMonthlyReport($request);
        $exportData = [];
        foreach ($monthlyReportData as $data) {
            $antrian = $data->token_letter . '-' . $data->token_number;
            if ($data->status == 'served') {
                $status = 'Dilayani';
            } elseif($data->status == 'noshow') {
                $status = 'Tidak Hadir';
            }else{
                $status = 'Menunggu';
            }
            $exportData[] = [
                'Pengguna' => $data->user_name,
                'Nomor Antrian' => $antrian,
                'Layanan' => $data->service_name,
                'Nama' => $data->queue_name,
                'NIK' => $data->queue_nik,
                'Telp' => $data->queue_phone,
                'Status Antrian' => $data->status_queue,
                'Loket' => $data->counter_name,
                'Tanggal' => $data->date,
                'Disebut Pada' => $data->called_at,
                'Dilayanani Pada' => $data->served_at,
                'Waktu Menunggu' => $data->waiting_time,
                'Waktu Penyajian' => $data->served_time,
                'Total Waktu' => $data->total_time,
                'Status' => $status,
            ];
        }

        $exportClass = new ExportMonthlyReportFromArray($exportData);

        return Excel::download($exportClass, $filename);
    }

    public function showReportNumber(Request $request)
    {
        $report = null;
        $starting_date = null;
        $ending_date = null;
        $services = Service::get();
        $service = null;
        if ($request->starting_date && $request->ending_date) {
            $starting_date = $request->starting_date;
            $ending_date = $request->ending_date;
            if (isset($request->service_id)) $service = $request->service_id;
            $report = $this->reportRepository->getReportNumbers($request);
        }
        return view('reports.report_number', ['report_numbers' => $report, 'services' => $services, 'timezone' => Setting::first()->timezone, 'selected' => ['starting_date' => $starting_date, 'ending_date' => $ending_date, 'service' => $service]]);
    }

    public function exportReportNumber(Request $request)
    {
        $dateToday = Carbon::now()->format('d-m-Y H:i:s');
        $filename = "Export_Report_Nomor_Antrian_{$dateToday}.xlsx";

        $reportNumberData = $this->reportRepository->getReportNumbers($request);
        $exportData = [];
        foreach ($reportNumberData as $data) {
            $exportData[] = [
                'Nomor' => $data->phone,
                'Total' => $data->total,
            ];
        }

        $exportClass = new ExportReportNumberFromArray($exportData);

        return Excel::download($exportClass, $filename);
    }

    public function sendMessage(Request $request)
    {
        $queueData = DB::table('queues_report')->where('id', $request->id)->first();
        $service = DB::table('services')->where('id', $queueData->service_id)->first();

        $reply_message = "Bukti Antrian Offline\n"
        . "Dinas Dukcapil Kab. Nganjuk\n\n"
        . "No Antrian : " . $service->letter . " - " . $queueData->number . "\n\n"
        . "Layanan : " . $service->name . "\n"
        . "Tanggal : " . date('d F Y H:i:s', strtotime($queueData->created_at)) . "\n"
        . "Tempat : Mall Pelayanan Publik Kab. Nganjuk\n\n"
        . "Silahkan datang pada tanggal yang tertera. Terima Kasih\n\n";
    
        if ($service->letter == 'A') {
            $reply_message .= "Catatan : 1 nomor antrian hanya untuk pencetakan 1 Keping KTP-EL.\n\n";
        }
        
        $reply_message .= "*_Mohon datang tepat waktu, Pelayanan sesuai dengan nomer pendaftaran._*\n";
        
                $post = [
                    'userId' => $queueData->phone,
                    'message' => $reply_message
                ];
                
                $curl_message = curl_init();
                curl_setopt_array($curl_message, array(
                    CURLOPT_URL => 'https://lasmini.cloud/api/sendMessagePhone',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($post),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Cookie: PHPSESSID=fib4rasu96joh5opks1ubre3g5'
                    ),
                ));
        
                $response_message = curl_exec($curl_message);
                curl_close($curl_message);  

                $request->session()->flash('success', 'Successfully send the message');
            return redirect()->route('queue_list_report');
    }
}

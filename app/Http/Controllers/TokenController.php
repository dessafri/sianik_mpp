<?php

namespace App\Http\Controllers;

use App\Jobs\SendSmsJob;
use App\Models\Call;
use App\Models\Queue;
use App\Models\Service;
use App\Models\OperationalTime;
use App\Models\Setting;
use App\Repositories\ServiceRepository;
use App\Repositories\OperationalRepository;
use App\Repositories\TokenRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use charlieuki\ReceiptPrinter\ReceiptPrinter as ReceiptPrinter;

class TokenController extends Controller
{
    public $services, $tokenRepository;

    public function __construct(ServiceRepository $services, TokenRepository $tokenRepository)
    {
        $this->services = $services;
        $this->tokenRepository = $tokenRepository;
    }

    //memanggil semua service dann menampilkan di index issue_token
    public function issueToken()
    {
        $date = now();
        $dayOfWeek = $date->format('l');
        $timeNow = $date->format('H:i:s');

        $operationalTime = OperationalTime::where('day', $dayOfWeek)
            ->where('status', 'Offline')
            ->where('on_time', '<=', $timeNow)
            ->where('off_time', '>=', $timeNow)
            ->first();

        $time = OperationalTime::where('day', $dayOfWeek)
            ->where('status', 'Offline')
            ->first();

        return view(
            'issue_token.index',
            [
                'services' => $this->services->getAllActiveServicesWithLimits(),
                'settings' => Setting::first(),
                'operationalTime' => $operationalTime,
                'time' => $time,
            ]
        );
    }

    public function onlineToken()
    {
        $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://lasmini.cloud/api/decrypt',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
            "token":"'.$_GET['q'].'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: PHPSESSID=fib4rasu96joh5opks1ubre3g5'
            ),
            ));

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response);
        $date_link = $data->data->date;

        if  ($date_link != Carbon::now()->format('Y-m-d')) {
            // return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Link tidak bisa digunakan']]]);
            echo "<script>alert('Maaf, Link tidak bisa digunakan');</script>";
        }else{
        
            $date = now();
            $dayOfWeek = $date->format('l');
            $timeNow = $date->format('H:i:s');

            $operationalTime = OperationalTime::where('day', $dayOfWeek)
                ->where('status', 'Online')
                ->where('on_time', '<=', $timeNow)
                ->where('off_time', '>=', $timeNow)
                ->first();

            $time = OperationalTime::where('day', $dayOfWeek)
                ->where('status', 'Online')
                ->first();

            return view(
                'online_token.index',
                [
                    'services' => $this->services->getAllActiveServicesWithLimitsOnline(),
                    'settings' => Setting::first(),
                    'operationalTime' => $operationalTime,
                    'time' => $time,
                ]
            );
        }
    }
    public function onlineTokenSimadu()
    {
        $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://lasmini.cloud/api/decrypt',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
            "token":"'.$_GET['q'].'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: PHPSESSID=fib4rasu96joh5opks1ubre3g5'
            ),
            ));

        $response = curl_exec($curl);
        curl_close($curl);
        $idSimadu = request('idSimadu');
        $tableName = request('table');
        $serviceid = request('serviceid');
        $tokenq = $_GET['q'];

        $data = json_decode($response);
        $date_link = $data->data->date;

        if  ($date_link != Carbon::now()->format('Y-m-d')) {
            // return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Link tidak bisa digunakan']]]);
            echo "<script>alert('Maaf, Link tidak bisa digunakan');</script>";
        }else{
        
            $date = now();
            $dayOfWeek = $date->format('l');
            $timeNow = $date->format('H:i:s');

            $operationalTime = OperationalTime::where('day', $dayOfWeek)
                ->where('status', 'Online')
                ->where('on_time', '<=', $timeNow)
                ->where('off_time', '>=', $timeNow)
                ->first();

            $time = OperationalTime::where('day', $dayOfWeek)
                ->where('status', 'Online')
                ->first();

            return view(
                'online_token.antrian',
                [
                    'services' => $this->services->getAllActiveServicesWithLimitsOnline(),
                    'settings' => Setting::first(),
                    'operationalTime' => $operationalTime,
                    'time' => $time,
                    'idSimadu' => $idSimadu,
                    'tableName' => $tableName,
                    'serviceid' => $serviceid,
                    'tokenq'=> $tokenq
                ]
            );
        }
    }

    //input ke antrian dan mendapatkan token
    public function createToken(Request $request, Service $service)
    {
        $date = now();

        $service_limit_type = DB::table('services')
            ->where('id', $request['service_id'])
            ->value('combined_limit');

        if ($service_limit_type == 1) {
            $totalQueueToday = Queue::whereDate('created_at', $date->toDateString())
                ->where('service_id', $request['service_id'])
                ->count();
            $queueLimit = DB::table('services')->where('id', $request['service_id'])->value('limit');
        } else {
            $totalQueueToday = Queue::whereDate('created_at', $date->toDateString())
                ->where('service_id', $request['service_id'])
                ->where('status_queue', 'Offline')
                ->count();
            $queueLimit = DB::table('services')->where('id', $request['service_id'])->value('offline_limit');
        }

        if ($totalQueueToday >= $queueLimit) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Antrian sudah Mencapai Limit.']]]);
        }

        $phoneNumber = $request['phone'];
        if (!empty($phoneNumber)) {
            $existingQueue = Queue::whereDate('created_at', $date->toDateString())
                ->where('phone', $phoneNumber)
                ->where('service_id', $request['service_id'])
                ->where('status_queue', 'Offline')
                ->exists();
            if ($existingQueue) {
                return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
            }

            $number = DB::table('blocked_numbers')
                ->where('phone_number', $phoneNumber)
                ->first();
            if ($number) {
                return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf Nomor Telepon terblokir.']]]);
            }

            $phone_list_url = "https://sianik.lasmini.cloud/api/phone-queue-list";
            $phone_list_json = file_get_contents($phone_list_url);
            $phone_list = json_decode($phone_list_json);
        
            foreach ($phone_list->antrian_list as $value) {
                $service = DB::table('services')
                ->where('id', $request['service_id'])
                ->first();
                
                if (($value->phone == $phoneNumber) && ($value->letter == $service->letter)) {
                    return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
                }
            }
        }

        DB::beginTransaction();
        try {
            $service = Service::findOrFail($request['service_id']);;

            $request->validate([
                'service_id' => 'required|exists:services,id',
                'with_details' => 'required',
                'name' => Rule::requiredIf(function () use ($request, $service) {
                    return $request->with_details && ($service->name_required == 1);
                }),
                'email' => [Rule::requiredIf(function () use ($request, $service) {
                    return $request->with_details && ($service->email_required == 1);
                })],
                'phone' => [Rule::requiredIf(function () use ($request, $service) {
                    return $request->with_details && ($service->email_required == 1);
                })],

            ]);
            $queue = $this->tokenRepository->createToken($service, $request->all(), $request->with_details ? true : false);
            $customer_waiting = $this->tokenRepository->customerWaiting($service);
            $customer_waiting = $customer_waiting > 0 ?  $customer_waiting - 1 : $customer_waiting;
            $settings = Setting::first();
            if ($service->sms_enabled && $service->optin_message_enabled && $queue->phone && $settings->sms_url) {
                SendSmsJob::dispatch($queue, $service->optin_message_format, $settings, 'issue_token');
            }
            $this->tokenRepository->setTokensOnFile();
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->errors();
                $message = $e->getMessage();
                return response()->json(['status_code' => 422, 'errors' => $errors]);
            }
            DB::rollback();
            return response()->json(['status_code' => 500]);
        }
        DB::commit();
        $queue = $queue->load('service');
        return response()->json(['status_code' => 200, 'queue' => $queue, 'customer_waiting' => $customer_waiting, 'settings' => $settings]);
    }
    public function createTokenOnline (Request $request, Service $service)
    {
        $date = now();

        $service_limit_type = DB::table('services')
            ->where('id', $request['service_id'])
            ->value('combined_limit');

        if ($service_limit_type == 1) {
            $totalQueueToday = Queue::whereDate('created_at', $date->toDateString())
                ->where('service_id', $request['service_id'])
                ->count();
            $queueLimit = DB::table('services')->where('id', $request['service_id'])->value('limit');
        } else {
            $totalQueueToday = Queue::whereDate('created_at', $date->toDateString())
                ->where('service_id', $request['service_id'])
                ->where('status_queue', 'Offline')
                ->count();
            $queueLimit = DB::table('services')->where('id', $request['service_id'])->value('offline_limit');
        }

        if ($totalQueueToday >= $queueLimit) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Antrian sudah Mencapai Limit.']]]);
        }

        $phoneNumber = $request['phone'];
        if (!empty($phoneNumber)) {
            $existingQueue = Queue::whereDate('created_at', $date->toDateString())
                ->where('phone', $phoneNumber)
                ->where('service_id', $request['service_id'])
                ->where('status_queue', 'Offline')
                ->exists();
            if ($existingQueue) {
                return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
            }

            $number = DB::table('blocked_numbers')
                ->where('phone_number', $phoneNumber)
                ->first();
            if ($number) {
                return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf Nomor Telepon terblokir.']]]);
            }

            $phone_list_url = "https://sianik.lasmini.cloud/api/phone-queue-list";
            $phone_list_json = file_get_contents($phone_list_url);
            $phone_list = json_decode($phone_list_json);
        
            foreach ($phone_list->antrian_list as $value) {
                $service = DB::table('services')
                ->where('id', $request['service_id'])
                ->first();
                
                if (($value->phone == $phoneNumber) && ($value->letter == $service->letter)) {
                    return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
                }
            }
        }

        DB::beginTransaction();
        try {
            $service = Service::findOrFail($request['service_id']);;

            $request->validate([
                'service_id' => 'required|exists:services,id',
                'with_details' => 'required',
                // 'name' => Rule::requiredIf(function () use ($request, $service) {
                //     return $request->with_details && ($service->name_required == 1);
                // }),
                // 'email' => [Rule::requiredIf(function () use ($request, $service) {
                //     return $request->with_details && ($service->email_required == 1);
                // })],
                // 'phone' => [Rule::requiredIf(function () use ($request, $service) {
                //     return $request->with_details && ($service->email_required == 1);
                // })],

            ]);
            $queue = $this->tokenRepository->createToken($service, $request->all(), $request->with_details ? true : false);
            $customer_waiting = $this->tokenRepository->customerWaiting($service);
            $customer_waiting = $customer_waiting > 0 ?  $customer_waiting - 1 : $customer_waiting;
            $settings = Setting::first();
            if ($service->sms_enabled && $service->optin_message_enabled && $queue->phone && $settings->sms_url) {
                SendSmsJob::dispatch($queue, $service->optin_message_format, $settings, 'issue_token');
            }
            $this->tokenRepository->setTokensOnFile();
        } catch (\Exception $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->errors();
                $message = $e->getMessage();
                return response()->json(['status_code' => 422, 'errors' => $errors]);
            }
            DB::rollback();
            return response()->json(['status_code' => 500]);
        }
        DB::commit();
        $queue = $queue->load('service');
        return response()->json(['status_code' => 200, 'queue' => $queue, 'customer_waiting' => $customer_waiting, 'settings' => $settings]);
    }

    // public function createTokenOnline(Request $request, Service $service)
    // {
    //     $date_link = $request->date_link;

    //     // Validasi batas maksimum antrian
    //     $date_inputan = date('Y-m-d', strtotime($request->date));

    //     if ($date_link != Carbon::now()->format('Y-m-d')) {
    //         return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Link tidak bisa digunakan']]]);
    //     }

    //     $service_limit_type = DB::table('services')
    //         ->where('id', $request->service_id)
    //         ->value('combined_limit');

    //     if ($service_limit_type == 1) {
    //         $totalQueueToday = Queue::whereDate('created_at', $date_inputan)
    //             ->where('service_id', $request->service_id)
    //             ->count();
    //         $queueLimit = DB::table('services')->where('id', $request->service_id)->value('limit');
    //     } else {
    //         $totalQueueToday = Queue::whereDate('created_at', $date_inputan)
    //             ->where('service_id', $request->service_id)
    //             ->where('status_queue', 'Online')
    //             ->count();
    //         $queueLimit = DB::table('services')->where('id', $request->service_id)->value('online_limit');
    //     }

    //     if ($totalQueueToday >= $queueLimit) {
    //         return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Antrian sudah Mencapai Limit.  Silahkan ambil Antrian di Hari Berikutnya']]]);
    //     }

    //     $phoneNumber = $request->phone;
    //     if (empty($phoneNumber)) {
    //         return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Nomor telepon tidak terdeteksi. Silahkan chat ulang Whatsapp LASMINI']]]);
    //     }

    //     $number = DB::table('blocked_numbers')
    //         ->where('phone_number', $phoneNumber)
    //         ->first();
    //     if ($number) {
    //         return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf Nomor Telepon terblokir.']]]);
    //     }

    //     $phone_list_url = "https://sianik.lasmini.cloud/api/phone-queue-list";
    //     $phone_list_json = file_get_contents($phone_list_url);
    //     $phone_list = json_decode($phone_list_json);
        
    //     foreach ($phone_list->antrian_list as $value) {
    //         $service = DB::table('services')
    //         ->where('id', $request->service_id)
    //         ->first();
            
    //         if (($value->phone == $phoneNumber) && ($value->letter == $service->letter)) {
    //             return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
    //         }
    //     }

    //     $existingQueue = Queue::whereDate('created_at', $date_inputan)
    //         ->where('phone', $phoneNumber)
    //         ->where('service_id', $request->service_id)
    //         ->exists();
    //     if ($existingQueue) {
    //         return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
    //     } else {
    //         DB::beginTransaction();
    //         try {
    //             $service = Service::findOrFail($request->service_id);

    //             $request->validate([
    //                 'service_id' => 'required|exists:services,id',
    //                 'with_details' => 'required',
    //                 'date' => [Rule::requiredIf(function () use ($request, $service) {
    //                     return $request->with_details && ($service->date_required == 1);
    //                 })],
    //             ]);

    //             // Proses pembuatan token hanya jika validasi berhasil dan jumlah antrian masih di bawah batas
    //             $queue = $this->tokenRepository->createTokenOnline($service, $request->all(), $request->with_details ? true : false);
    //             $customer_waiting = $this->tokenRepository->customerWaiting($service);
    //             $customer_waiting = $customer_waiting > 0 ?  $customer_waiting - 1 : $customer_waiting;
    //             $settings = Setting::first();

    //             if ($service->sms_enabled && $service->optin_message_enabled && $queue->phone && $settings->sms_url) {
    //                 SendSmsJob::dispatch($queue, $service->optin_message_format, $settings, 'issue_token');
    //             }

    //             $this->tokenRepository->setTokensOnFile();
    //         } catch (\Exception $e) {
    //             dd($e->getMessage());
    //             if ($e instanceof \Illuminate\Validation\ValidationException) {
    //                 $errors = $e->errors();
    //                 $message = $e->getMessage();
    //                 return response()->json(['status_code' => 422, 'errors' => $errors]);
    //             }
    //             DB::rollback();
    //             return response()->json(['status_code' => 500]);
    //         }
    //         DB::commit();
    //         $queue = $queue->load('service');
    //         return response()->json(['status_code' => 200, 'queue' => $queue, 'customer_waiting' => $customer_waiting, 'settings' => $settings]);
    //     }
    // }
    public function createTokenOnlineSimadu(Request $request, Service $service)
    {
        $date_link = $request->date_link;
        dd($request['service_id']);

        // Validasi batas maksimum antrian
        $date_inputan = date('Y-m-d', strtotime($request->date));

        if ($date_link != Carbon::now()->format('Y-m-d')) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Link tidak bisa digunakan']]]);
        }

        $service_limit_type = DB::table('services')
            ->where('id', $request->service_id)
            ->value('combined_limit');

        if ($service_limit_type == 1) {
            $totalQueueToday = Queue::whereDate('created_at', $date_inputan)
                ->where('service_id', $request->service_id)
                ->count();
            $queueLimit = DB::table('services')->where('id', $request->service_id)->value('limit');
        } else {
            $totalQueueToday = Queue::whereDate('created_at', $date_inputan)
                ->where('service_id', $request->service_id)
                ->where('status_queue', 'Online')
                ->count();
            $queueLimit = DB::table('services')->where('id', $request->service_id)->value('online_limit');
        }

        if ($totalQueueToday >= $queueLimit) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, Antrian sudah Mencapai Limit.  Silahkan ambil Antrian di Hari Berikutnya']]]);
        }

        $phoneNumber = $request->phone;
        if (empty($phoneNumber)) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Nomor telepon tidak terdeteksi. Silahkan chat ulang Whatsapp LASMINI']]]);
        }

        $number = DB::table('blocked_numbers')
            ->where('phone_number', $phoneNumber)
            ->first();
        if ($number) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf Nomor Telepon terblokir.']]]);
        }

        $phone_list_url = "https://sianik.lasmini.cloud/api/phone-queue-list";
        $phone_list_json = file_get_contents($phone_list_url);
        $phone_list = json_decode($phone_list_json);
        
        foreach ($phone_list->antrian_list as $value) {
            $service = DB::table('services')
            ->where('id', $request->service_id)
            ->first();
            
            if (($value->phone == $phoneNumber) && ($value->letter == $service->letter)) {
                return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
            }
        }

        $existingQueue = Queue::whereDate('created_at', $date_inputan)
            ->where('phone', $phoneNumber)
            ->where('service_id', $request->service_id)
            ->exists();
        if ($existingQueue) {
            return response()->json(['status_code' => 422, 'errors' => ['limit' => ['Maaf, nomor telepon ini sudah membuat antrian pada tanggal ini.']]]);
        } else {
            DB::beginTransaction();
            try {
                $service = Service::findOrFail($request->service_id);

                // $request->validate([
                //     'service_id' => 'required|exists:services,id',
                //     'with_details' => 'required',
                //     'date' => [Rule::requiredIf(function () use ($request, $service) {
                //         return $request->with_details && ($service->date_required == 1);
                //     })],
                // ]);

                // Proses pembuatan token hanya jika validasi berhasil dan jumlah antrian masih di bawah batas
                $queue = $this->tokenRepository->createTokenOnline($service, $request->all(), $request->with_details ? true : false);
                $customer_waiting = $this->tokenRepository->customerWaiting($service);
                $customer_waiting = $customer_waiting > 0 ?  $customer_waiting - 1 : $customer_waiting;
                $settings = Setting::first();

                if ($service->sms_enabled && $service->optin_message_enabled && $queue->phone && $settings->sms_url) {
                    SendSmsJob::dispatch($queue, $service->optin_message_format, $settings, 'issue_token');
                }

                $this->tokenRepository->setTokensOnFile();
            } catch (\Exception $e) {
                dd($e->getMessage());
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    $errors = $e->errors();
                    $message = $e->getMessage();
                    return response()->json(['status_code' => 422, 'errors' => $errors]);
                }
                DB::rollback();
                return response()->json(['status_code' => 500]);
            }
            DB::commit();
            $queue = $queue->load('service');
            return response()->json(['status_code' => 200, 'queue' => $queue, 'customer_waiting' => $customer_waiting, 'settings' => $settings]);
        }
    }

    public function printToken(Request $request)
    {
        // dump($request);exit;
        // $queue = $queue->load('service');
        // dd($settings);exit;
        $printer = new ReceiptPrinter;

        $printer->init(
            config('receiptprinter.connector_type'),
            config('receiptprinter.connector_descriptor')
        );
        $printer->setQueue($request->name, $request->location, $request->service_name, $request->que_letter, $request->que_number, $request->que_date, $request->customer_waiting);
        // dd($request);
        //dd($settings); exit;
        $printer->printReceiptQueue();
        dd($request);


        return response()->json(['status_code' => 200]);
    }
}

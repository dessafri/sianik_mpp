<?php

namespace App\Repositories;

use App\Models\Call;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Support\Str;
use App\Repositories\ServiceRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TokenRepository
{
    public $services;
    public function __construct(ServiceRepository $services)
    {
        $this->services = $services;
    }

    public function createToken(Service $service, $data, $is_details)
    {
        // dd($data['IdSimadu']);
        try {
            DB::beginTransaction();
            $currentTime = now()->toDateString();
            $endOfDay = now()->endOfDay(); 
            
            $last_token_count = Queue::where('created_at', '>=', $currentTime)
                ->where('created_at', '<', $endOfDay)
                ->where('service_id', $service->id)
                ->count();
            
            $token_number = ($last_token_count) ? $last_token_count + 1 : $service->start_number;
            
            $queue = Queue::create([
                'service_id' => $service->id,
                'number' => $token_number,
                'called' => false,
                'reference_no' => Str::random(9),
                'letter' => $service->letter,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'position' => $this->customerWaiting($service) + 1,
                'nik' => $data['nik'],
                'status_queue' => "Offline",
                "IdSimadu" => $data['idSimadu'],
                "tableNameSimadu" => $data['tableName'],
            ]);
            $queueId = $queue->id;
            $this->insertQueuesToReport($queueId);

            $services = $this->services->getServiceById($service->id);
            if (!empty($data['phone'])) {        
                $reply_message = "Bukti Antrian Online\n"
                . "Dinas Dukcapil Kab. Nganjuk\n\n"
                . "No Antrian : ".$service->letter." - ".$token_number."\n\n"
                . "Layanan : ".$services['name']."\n"
                . "Tanggal : " . date('d F Y H:i:s') . "\n"
                . "Tempat : Mall Pelayanan Publik Kab. Nganjuk\n\n"
                . "Silahkan datang pada tanggal yang tertera. Terima Kasih\n\n";

                if ($service->letter == 'A') {
                    $reply_message .= "Catatan : 1 nomor antrian hanya untuk pencetakan 1 Keping KTP-EL.\n\n";
                }
                
                $reply_message .= "*_Mohon datang tepat waktu, Pelayanan sesuai dengan nomer pendaftaran._*\n";
        
                $post = [
                    'userId' => $data['phone'],
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
            }

            DB::commit();
            return $queue;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error occurred: ' . $e->getMessage());
            return response()->json(['status_code' => 500, 'message' => 'Internal Server Error']);
        }
    }

    public function createTokenOnline(Service $service, $data, $is_details)
    {
        // dd($data);
        try {
            DB::beginTransaction();
            $currentTime = now()->toDateString();
            $endOfDay = now()->endOfDay(); 
                
            $last_token_count = Queue::where('created_at', '>=', $currentTime)
                ->where('created_at', '<', $endOfDay)
                ->where('service_id', $data['service_id'])
                ->count();
                
            if ($last_token_count) {
                $token_number = $last_token_count + 1;
            } else {
                $token_number = $service->start_number;
            }
            
            DB::commit();

            $queue = Queue::create([
                'service_id' => $data['service_id'],
                'number' => $token_number,
                'called' => false,
                'reference_no' => Str::random(9),
                'letter' => $service->letter,
                'name' => ($data['name'] != '') ? $data['name'] : null,
                'email' => ($data['email'] != '') ? $data['email'] : null,
                'phone' => ($data['phone'] != '') ? $data['phone'] : null,
                'position' => $this->customerWaiting($service) + 1,
                'created_at' => $data['date'],
                'updated_at' => $data['date'],
                'nik' => $data['nik'],
                'IdSimadu' => $data['idSimadu'],
                'nik' => $data['tableName'],
                'status_queue' => "Online"
            ]);
            $queueId = $queue->id;
            $this->insertQueuesToReport($queueId);

            $services = $this->services->getServiceById($service->id);
    
            $reply_message = "Bukti Antrian Online\n"
                    . "Dinas Dukcapil Kab. Nganjuk\n\n"
                    . "Atas Nama : ".$data['name']."\n"
                    . "Layanan : ".$services['name']."\n"
                    . "Antrian : ".$service->letter." - ".$token_number."\n"
                    . "Tanggal : " . date('d F Y H:i:s', strtotime($data['date'])) . "\n"
                    . "Tempat : Mall Pelayanan Publik Kab. Nganjuk\n\n"
                    . "Silahkan datang pada tanggal yang tertera. Terima Kasih\n\n";
    
                    if ($service->letter == 'A') {
                        $reply_message .= "Catatan :  1 nomor antrian hanya untuk pencetakan 1 Keping KTP-EL. Bila mau mencetak lebih dari 1 keping maka silahkan ambil nomor antrian kembali dengan nomor Whatsapp yang berbeda\n\n";
                    }
                    
                    $reply_message .= "*_Mohon datang tepat waktu, Pelayanan sesuai dengan nomer pendaftaran, apabila 3x panggilan tidak ada, maka akan dilayani setelah no antrian terakhir._*\n";

            $post = [
                'userId' => $data['email'],
                'message' => $reply_message
            ];

            $curl_message = curl_init();

            curl_setopt_array($curl_message, array(
                CURLOPT_URL => 'https://lasmini.cloud/api/sendMessage',
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
            
            return $queue;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error occurred: ' . $e->getMessage());
            return response()->json(['status_code' => 500, 'message' => 'Internal Server Error']);
        }
    }

    public function customerWaiting(Service $service)
    {
        $count = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())
            ->where('called', false)->where('service_id', $service->id)->count();
        return $count;
    }

    public function getTokensForCall($service)
    {
        $tokens = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())
            ->where('called', false)->where('service_id', $service->id)->get()->toArray();
        return $tokens;
    }

    public function getCalledTokens($service, $counter)
    {
        // $tokens =  Call::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())
        //     ->where('service_id', $service->id)->where('counter_id', $counter->id)->orderByDesc('created_at')->get()->toArray();
        
        $tokens = Call::join('queues', 'calls.queue_id', '=', 'queues.id')
        ->select('calls.*', 'queues.name as queue_name', 'queues.nik as queue_nik', 'queues.phone as queue_phone')
        ->where('calls.created_at', '>=', Carbon::now()->startOfDay())
        ->where('calls.created_at', '<=', Carbon::now())
        ->where('calls.service_id', $service->id)
        ->where('calls.counter_id', $counter->id)
        ->orderByDesc('calls.created_at')
        ->get()
        ->toArray();
        return $tokens;
    }

    public function setTokensOnFile()
    {
        $tokens_for_call = Queue::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())
            ->where('called', false)->get()->toArray();
        $called_tokens =  Call::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())
            ->orderByDesc('created_at')->get()->toArray();
        $data['tokens_for_call'] = $tokens_for_call;
        $data['called_tokens'] = $called_tokens;
        Storage::put('public/tokens_for_callpage.json', json_encode($data));
    }

    public function insertQueuesToReport($queueId)
    {
        $queues = Queue::find($queueId);
    
        DB::table('queues_report')->insert([
            'id' => $queues->id,
            'service_id' => $queues->service_id,
            'number' => $queues->number,
            'called' => $queues->called,
            'letter' => $queues->letter,
            'reference_no' => $queues->reference_no,
            'phone' => $queues->phone,
            'email' => $queues->email,
            'name' => $queues->name,
            'position' => $queues->position,
            'created_at' => $queues->created_at,
            'updated_at' => $queues->updated_at,
            'nik' => $queues->nik,
            'status_queue' => $queues->status_queue,
        ]);
    }

    public function getPhoneQueueList()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        return Queue::join('services', 'queues.service_id', '=', 'services.id')
            ->where('queues.created_at', '>=', $today)
            ->where('queues.created_at', '<', $tomorrow)
            ->where('queues.phone', '!=', NULL)
            ->select('queues.phone','queues.letter','queues.service_id','services.name')
        ->get();
    }
}

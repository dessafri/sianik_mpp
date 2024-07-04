<?php

namespace App\Repositories;

use App\Consts\CallStatuses;
use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\DB;
use App\Models\Call;
use App\Models\Queue;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CallRepository
{

    public function callNext($service_id, $counter_id)
    {
        $already_called =  Call::where('created_at', '>=', Carbon::now()->startOfDay())
            ->where('created_at', '<=', Carbon::now())
            ->where('service_id', $service_id)
            ->where('counter_id', $counter_id)
            ->whereNull('call_status_id')
            ->first();

        if ($already_called) {
            return $already_called;
        } else {
            $queue = Queue::where('created_at', '>=', Carbon::now()->startOfDay())
                ->where('created_at', '<=', Carbon::now())
                ->where('called', false)
                ->where('service_id', $service_id)
                ->first();

            if ($queue) {
                $called_position = $queue->position;
                $call = Call::create([
                    'queue_id' => $queue->id,
                    'service_id' => $queue->service_id,
                    'counter_id' => session()->get('counter')->id,
                    'user_id' => Auth::user()->id,
                    'token_letter' => $queue->letter,
                    'token_number' => $queue->number,
                    'called_date' => Carbon::now()->toDateString(),
                    'started_at' => Carbon::now(),
                    'waiting_time' => $queue->created_at->diff(Carbon::now())->format('%H:%I:%S')
                ]);
                $callId = $call->id;
                $this->insertCallsToReport($callId);

                $queue->position = 0;
                $queue->called = true;
                $queue->save();

                $this->UpdateCallsStatus($queue['id']);

                $this->decrementPostion($queue->service_id, $called_position);

                return $call;
            } else return null;
        }
    }

    public function callnextTokenById($id, $service_id)
    {
        $queue = Queue::where('created_at', '>=', Carbon::now()->startOfDay())
            ->where('created_at', '<=', Carbon::now())
            ->where('called', false)
            ->where('id', $id)
            ->where('service_id', $service_id)
            ->first();
        if ($queue) {
            $called_position = $queue->position;
            $call = Call::create([
                'queue_id' => $queue->id,
                'service_id' => $queue->service_id,
                'counter_id' => session()->get('counter')->id,
                'user_id' => Auth::user()->id,
                'token_letter' => $queue->letter,
                'token_number' => $queue->number,
                'called_date' => Carbon::now()->toDateString(),
                'started_at' => Carbon::now(),
                'waiting_time' => $queue->created_at->diff(Carbon::now())->format('%H:%I:%S')
            ]);
            $callId = $call->id;
            $this->insertCallsToReport($callId);

            $queue->called = true;
            $queue->position = 0;
            $queue->save();

            $this->UpdateCallsStatus($queue['id']);

            $this->decrementPostion($queue->service_id, $called_position);

            return $call;
        } else return null;
    }

    public function serveToken(Call $call)
    {
        $call->ended_at = Carbon::now();
        $call->served_time = Carbon::parse($call->started_at)->diff(Carbon::now())->format('%H:%I:%S');
        $call->turn_around_time = Carbon::parse($call->waiting_time)->add(Carbon::parse($call->started_at)->diff(Carbon::now()))->toTimeString();
        $call->call_status_id = CallStatuses::SERVED;
        $call->save();

        DB::table('calls_report')
        ->where('id', $call['id'])
        ->update([
            'call_status_id' => 1,
            'ended_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'served_time' => Carbon::parse($call->started_at)->diff(Carbon::now())->format('%H:%I:%S'),
            'turn_around_time' => Carbon::parse($call->waiting_time)->add(Carbon::parse($call->started_at)->diff(Carbon::now()))->toTimeString(),
        ]);

        return $call;
    }

    public function noShowToken(Call $call)
    {
        $call->ended_at = Carbon::now()->format('Y-m-d H:i:s');
        $call->call_status_id = CallStatuses::NOSHOW;
        $call->save();

        DB::table('calls_report')
        ->where('id', $call['id'])
        ->update([
            'call_status_id' => 2,
            'updated_at' => Carbon::now(),
            'ended_at' => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        return $call;
    }

    public function recallToken(Call $call)
    {
        $copy = $call->replicate();
        $call->delete();
        $new_call = Call::create([
            'queue_id' => $copy->queue_id,
            'service_id' => $copy->service_id,
            'counter_id' => $copy->counter_id,
            'user_id' => $copy->user_id,
            'token_letter' => $copy->token_letter,
            'token_number' => $copy->token_number,
            'called_date' => $copy->called_date,
            'started_at' => $copy->started_at,
            'waiting_time' => $copy->waiting_time,
        ]);
        $newCallId = $new_call->id;

        DB::table('calls_report')
        ->where('id', $call['id'])
        ->delete();

        $this->insertCallsToReport($newCallId);

        return $new_call;
    }

    public function setCallsForDisplay($service)
    {
        $data = json_encode(Call::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())->orderByDesc('id')->with('counter')->limit(5)->get()->toArray());
        Storage::put('public/tokens_for_display.json', $data);

        $service_data = json_encode(Call::where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())->where('service_id', $service->id)->orderByDesc('id')->with('counter')->limit(5)->get()->toArray());
        Storage::put('public/service_' . $service->id . '_display.json', $service_data);
    }

    public function getCallsForDisplay()
    {
        $data=Call::with('service')->where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())->orderByDesc('id')->with('counter')->limit(5)->get()->toArray();
        return $data;
    }

    public function getCallsForDisplayServices()
    {
        $data=Call::with('service')->where('created_at', '>=', Carbon::now()->startOfDay())->where('created_at', '<=', Carbon::now())->orderByDesc('id')->with('counter')->get()->toArray();
        return $data;
    }

    public function getCallsForDisplay2()
    {
        $services = Service::select('services.id', 'services.name', 'calls.call_status_id', 'calls.counter_id', 'calls.queue_id', 'counters.name as countername')
                    ->where('services.status_online', true)
                    ->Orwhere('services.status', '>=', true)
                    ->join('calls', 'calls.service_id', '=', 'services.id')
                    // ->where('created_at', '>=', Carbon::now()->startOfDay())
                    // ->where('created_at', '<=', Carbon::now())
                    ->join(DB::raw('(SELECT service_id, MAX(id) as max_id FROM calls GROUP BY service_id) AS m'), function ($join) {
                        $join->on('m.service_id', '=', 'services.id');
                    })
                    ->join('counters', 'counters.id', '=', 'calls.counter_id')
                    ->groupBy('services.id', 'services.name', 'calls.call_status_id', 'calls.counter_id', 'calls.queue_id', 'counters.name')
                    ->get();

                    $data = [];
                    foreach ($services as $value) {
                        $callStatus = $value->call_status_id;
                        $counterName = $value->countername;
                        $queueName = $value->queue_id;
                    
                        $data[] = [
                            'service' => $value->name,
                            'calls' => $callStatus,
                            'counter' => $counterName,
                            'queue' => $queueName
                        ];
                    }
                    // dd(json_encode($data));
                    return $data;
    }

    public function getCallsForAntrian()
    {
        $data=Call::with('service','counter')
        ->where('created_at', '>=', Carbon::now()->startOfDay())
        ->where('created_at', '<=', Carbon::now())
        ->orderByDesc('id')
        ->limit(9)
        ->get()
        ->toArray();
        return $data;
    }

    public function getCallsForToday()
    {
        $calls = Call::whereDate('created_at', '>=', now()->toDateString())
                    ->whereDate('created_at', '<', now()->addDay()->toDateString())
                    ->get();

        return $calls;
    }

    public function getTokenForCallNext($service_id, $counter_id)
    {
        $already_called = Call::where('created_at', '>=', Carbon::now()->startOfDay())
            ->where('created_at', '<=', Carbon::now())
            ->where('service_id', $service_id)
            ->where('service_id', $$counter_id)
            ->whereNull('call_status_id')
            ->first();
        if ($already_called) {
            return $already_called;
        } else {
            $token = Queue::where('created_at', '>=', Carbon::now()->startOfDay())
                ->where('created_at', '<=', Carbon::now())
                ->where('called', false)
                ->where('service_id', $service_id)
                ->where('service_id', $$counter_id)
                ->first();

            return $token;
        }
    }

    public function decrementPostion($service_id, $called_position)
    {
        Queue::where('created_at', '>=', Carbon::now()->startOfDay())
            ->where('created_at', '<=', Carbon::now())
            ->where('position', '>', $called_position)
            ->where('service_id', $service_id)
            ->decrement('position');
    }

    public function sendStatusMessage($queue, $position, $settings)
    {
        $queue = Queue::where('created_at', '>=', Carbon::now()->startOfDay())
            ->where('created_at', '<=', Carbon::now())
            ->where('service_id', $queue->service_id)
            ->where('position', $position)
            ->first();
        if ($queue) SendSmsJob::dispatch($queue, $queue->service->status_message_format, $settings, 'status_message');
    }

    public function insertCallsToReport($callId)
    {
        $call = Call::find($callId);
    
        DB::table('calls_report')->insert([
            'id' => $call->id,
            'queue_id' => $call->queue_id,
            'service_id' => $call->service_id,
            'counter_id' => $call->counter_id,
            'user_id' => $call->user_id,
            'token_letter' => $call->token_letter,
            'token_number' => $call->token_number,
            'called_date' => $call->called_date,
            'started_at' => $call->started_at,
            'ended_at' => $call->ended_at,
            'waiting_time' => $call->waiting_time,
            'served_time' => $call->served_time,
            'turn_around_time' => $call->turn_around_time,
            'created_at' => $call->created_at,
            'updated_at' => $call->updated_at,
            'call_status_id' => $call->call_status_id,
        ]);
    }

    public function UpdateCallsStatus($queueId)
    {
        DB::table('queues_report')
        ->where('id', $queueId)
        ->update([
            'called' => true,
            'position' => 0,
            'updated_at' => Carbon::now(),
        ]);
    }
}
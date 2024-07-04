<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ServiceRepository
{
    public function getAllServices()
    {
        return Service::get();
    }

    public function getAllActiveServices()
    {
        return Service::where('status', true)
            ->orWhere('status_online', true)
            ->get();
    }

    public function getAllActiveServicesOffline()
    {
        return Service::where('status', true)->get();
    }

    public function getAllActiveServicesOnline()
    {
        return Service::where('status_online', true)->get();
    }

    public function getAllActiveServicesWithLimits()
    {
        $today = Carbon::today();

        return DB::table('services')
            ->select('services.*')
            ->where('services.status', '=', 1)
            ->get()
            ->map(function ($service) use ($today) {
                if ($service->combined_limit == 1) {
                    $queues = DB::table('queues')
                        ->whereDate('queues.created_at', '=', $today)
                        ->where('service_id', '=', $service->id)
                        ->count();
                    $limit = $service->limit - $queues;
                } else {
                    $queues = DB::table('queues')
                        ->whereDate('queues.created_at', '=', $today)
                        ->where('status_queue', 'Offline')
                        ->where('service_id', '=', $service->id)
                        ->count();
                    $limit = $service->offline_limit - $queues;
                }

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'letter' => $service->letter,
                    'start_number' => $service->start_number,
                    'status' => $service->status,
                    'sms_enabled' => $service->sms_enabled,
                    'optin_message_enabled' => $service->optin_message_enabled,
                    'call_message_enabled' => $service->call_message_enabled,
                    'noshow_message_enabled' => $service->noshow_message_enabled,
                    'completed_message_enabled' => $service->completed_message_enabled,
                    'status_message_enabled' => $service->status_message_enabled,
                    'optin_message_format' => $service->optin_message_format,
                    'call_message_format' => $service->call_message_format,
                    'noshow_message_format' => $service->noshow_message_format,
                    'completed_message_format' => $service->completed_message_format,
                    'status_message_format' => $service->status_message_format,
                    'status_message_positions' => $service->status_message_positions,
                    'ask_name' => $service->ask_name,
                    'name_required' => $service->name_required,
                    'ask_email' => $service->ask_email,
                    'email_required' => $service->email_required,
                    'ask_phone' => $service->ask_phone,
                    'phone_required' => $service->phone_required,
                    'created_at' => $service->created_at,
                    'updated_at' => $service->updated_at,
                    'offline_limit' => $service->offline_limit,
                    'online_limit' => $service->online_limit,
                    'status_online' => $service->status_online,
                    'ask_nik' => $service->ask_nik,
                    'combined_limit' => $service->combined_limit,
                    'remaining_limit' => $limit,
                ];
            });
    }

    public function getAllActiveServicesWithLimitsOnline()
    {
        $today = Carbon::today();

        return DB::table('services')
            ->select('services.*')
            ->where('services.status_online', '=', 1)
            ->get()
            ->map(function ($service) use ($today) {
                if ($service->combined_limit == 1) {
                    $queues = DB::table('queues')
                        ->whereDate('queues.created_at', '=', $today)
                        ->where('service_id', '=', $service->id)
                        ->count();
                    $limit = $service->limit - $queues;
                } else {
                    $queues = DB::table('queues')
                        ->whereDate('queues.created_at', '=', $today)
                        ->where('status_queue', 'Online')
                        ->where('service_id', '=', $service->id)
                        ->count();
                    $limit = $service->online_limit - $queues;
                }

                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'letter' => $service->letter,
                    'start_number' => $service->start_number,
                    'status' => $service->status,
                    'sms_enabled' => $service->sms_enabled,
                    'optin_message_enabled' => $service->optin_message_enabled,
                    'call_message_enabled' => $service->call_message_enabled,
                    'noshow_message_enabled' => $service->noshow_message_enabled,
                    'completed_message_enabled' => $service->completed_message_enabled,
                    'status_message_enabled' => $service->status_message_enabled,
                    'optin_message_format' => $service->optin_message_format,
                    'call_message_format' => $service->call_message_format,
                    'noshow_message_format' => $service->noshow_message_format,
                    'completed_message_format' => $service->completed_message_format,
                    'status_message_format' => $service->status_message_format,
                    'status_message_positions' => $service->status_message_positions,
                    'ask_name' => $service->ask_name,
                    'name_required' => $service->name_required,
                    'ask_email' => $service->ask_email,
                    'email_required' => $service->email_required,
                    'ask_phone' => $service->ask_phone,
                    'phone_required' => $service->phone_required,
                    'created_at' => $service->created_at,
                    'updated_at' => $service->updated_at,
                    'offline_limit' => $service->offline_limit,
                    'online_limit' => $service->online_limit,
                    'status_online' => $service->status_online,
                    'ask_nik' => $service->ask_nik,
                    'combined_limit' => $service->combined_limit,
                    'remaining_limit' => $limit,
                ];
            });
    }
    
    public function getCallsForAntrian()
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();
        
        return DB::table('services')
        ->where('services.status_online', true)
        ->Orwhere('services.status', '>=', true)
        ->leftJoin('queues', 'services.id', '=', 'queues.service_id')
        ->where('queues.created_at', '>=', $today)
        ->where('queues.created_at', '<', $tomorrow)
        ->select(
            'services.*',
            DB::raw('MAX(CASE WHEN queues.called = 1 THEN queues.letter ELSE NULL END) AS letter_called'),
            DB::raw('MAX(CASE WHEN queues.called = 1 THEN queues.number ELSE NULL END) AS number_called'),
        )
        ->groupBy('services.id')
        ->get()->toArray();
    }

    public function getServiceById($id)
    {
        return Service::find($id);
    }
    
    public function create($data)
    {
        if (!isset($data['sms'])) $data['sms'] = false;
        $service = Service::create([
            'name' => $data['name'],
            'letter' => $data['letter'],
            'start_number' => $data['start_number'],
            'status' => 1,
            'status_online' => 1,
            'ask_phone' => $data['ask_phone'],
            'phone_required' => $data['ask_phone'] == 1 ?  $data['phone_required'] : false,
            'sms_enabled' => $data['ask_phone'] == 1 ? $data['sms'] : false,
            'optin_message_enabled' => ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['optin_message'] : false,
            'call_message_enabled' => ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['call_message'] : false,
            'noshow_message_enabled' => ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['noshow_message'] : false,
            'completed_message_enabled' => ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['completed_message'] : false,
            'status_message_enabled' => ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['status_message'] : false,
            'optin_message_format' => ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['optin_message'] == 1) ? str_replace("'", "`", $data['optin_message_format']) : null,
            'call_message_format' => ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['call_message'] == 1) ? str_replace("'", "`", $data['call_message_format']) : null,
            'noshow_message_format' => ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['noshow_message'] == 1) ? str_replace("'", "`", $data['noshow_message_format']) : null,
            'completed_message_format' => ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['completed_message'] == 1) ? str_replace("'", "`", $data['completed_message_format']) : null,
            'status_message_format' => ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['status_message'] == 1) ? str_replace("'", "`", $data['status_message_format']) : null,
            'status_message_positions' => ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['status_message'] == 1) ?  $data['status_message_positions'] : null,
            'ask_name' => $data['ask_name'],
            'name_required' => $data['ask_name'] == 1 ? $data['name_required'] : false,
            'ask_email' => $data['ask_email'],
            'email_required' => $data['ask_email'] == 1 ? $data['email_required'] : false,
            'offline_limit' => $data['offline_limit'],
            'online_limit' => $data['online_limit'],
            'ask_nik' => $data['ask_nik'],
            'combined_limit' => $data['combined_limit'],
            'limit' => $data['limit'],
        ]);
        return $service;
    }

    public function update($data, $service)
    {
        if (!isset($data['sms'])) $data['sms'] = false;

        $service->name = $data['name'];
        $service->letter = $data['letter'];
        $service->start_number = $data['start_number'];
        $service->sms_enabled = $data['sms'];
        $service->optin_message_enabled = ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['optin_message'] : false;
        $service->call_message_enabled = ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['call_message'] : false;
        $service->noshow_message_enabled = ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['noshow_message'] : false;
        $service->completed_message_enabled = ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['completed_message'] : false;
        $service->status_message_enabled = ($data['ask_phone'] == 1 && $data['sms'] == 1) ? $data['status_message'] : false;
        $service->optin_message_format = ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['optin_message'] == 1) ? str_replace("'", "`", $data['optin_message_format']) : null;
        $service->call_message_format = ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['call_message'] == 1) ? str_replace("'", "`", $data['call_message_format']) : null;
        $service->noshow_message_format = ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['noshow_message'] == 1) ? str_replace("'", "`", $data['noshow_message_format'])  : null;
        $service->completed_message_format = ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['completed_message'] == 1) ? str_replace("'", "`", $data['completed_message_format']) : null;
        $service->status_message_format = ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['status_message'] == 1) ? str_replace("'", "`", $data['status_message_format']) : null;
        $service->status_message_positions = ($data['ask_phone'] == 1 && $data['sms'] == 1 && $data['status_message'] == 1) ? $data['status_message_positions'] : null;
        $service->ask_name = $data['ask_name'];
        $service->name_required = ($data['ask_name'] == 1) ? $data['name_required'] : false;
        $service->ask_email = $data['ask_email'];
        $service->email_required = ($data['ask_email'] == 1) ? $data['email_required'] : false;
        $service->ask_phone = $data['ask_phone'];
        $service->phone_required = ($data['ask_phone'] == 1) ? $data['phone_required'] : false;
        $service->online_limit = $data['online_limit'];
        $service->offline_limit = $data['offline_limit'];
        $service->ask_nik = $data['ask_nik'];
        $service->combined_limit = $data['combined_limit'];
        $service->limit = $data['limit'];
        $service->save();
        return $service;
    }

    public function delete($data, $service)
    {
        Storage::delete('public/service_' . $service->id . '_display.json');
        $service->delete();
    }
}
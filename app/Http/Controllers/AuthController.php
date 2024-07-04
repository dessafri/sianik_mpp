<?php

namespace App\Http\Controllers;

use App\Consts\AppVersion;
use App\Http\Controllers\Controller;
use App\Models\Session;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use App\Models\Queue;
use App\Models\Call;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class AuthController extends Controller
{
    public function home()
    {
        if (auth()->guard('web')->check()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }

    public function index()
    {
        if (Setting::first() && Setting::first()->installed == 1) $this->removeInstallationFile(Setting::first());

        return view('login.login');
    }

    public function authenticate(Request $request)
    {

        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            $request->session()->regenerate();
            $settings = Setting::first();
            session(['settings' => $settings]);
            if ($settings->language_id) {
                session(['locale' => $settings->language->code]);
            }
            if ($user->role_id == 2) {
                return redirect()->route('show_call_page');
            } else {
                return redirect()->intended('dashboard')->with('success', 'Succesfully Logged in');
            }
        }

        return back()->withErrors([
            'error' => 'The provided credentials do not match our records.',
        ]);
    }
    
    public function logout()
    {
        session()->invalidate();
        Auth::guard('web')->logout();
        return redirect()->route('dashboard');
    }

    public function setEnv()
    {
        file_put_contents(app()->environmentFilePath(), str_replace(
            'SESSION_DRIVER=file',
            'SESSION_DRIVER=database',
            file_get_contents(app()->environmentFilePath())
        ));
    }

    public function filesCurrupted(Request $request)
    {
        return view('vendor.installer.file-currupted', ['app_version' => AppVersion::VERSION]);
    }

    public function removeInstallationFile(Setting $settings)
    {
        File::delete(base_path('/app/Http/Controllers/InstallerController.php'));
        File::delete(base_path('/app/Repositories/InstallerRepository.php'));
        File::delete(base_path('/config/installer.php'));
        $data = '<?php
        ';
        file_put_contents(base_path('/routes/install.php'), $data);
        $settings->installed = 2;
        $settings->save();
        try{
            Artisan::call('optimize');
            Artisan::call('route:clear');
        }catch(Throwable $th){
            
        }
        
        return $settings;
    }

    public function insertQueuesToReport()
    {
        $queues = Queue::get();
    
        foreach ($queues as $queue) {
            $existingRecord = DB::table('queues_report')
                ->where('id', $queue->id)
                ->exists();
    
            if (!$existingRecord) {
                DB::table('queues_report')->insert([
                    'id' => $queue->id,
                    'service_id' => $queue->service_id,
                    'number' => $queue->number,
                    'called' => $queue->called,
                    'letter' => $queue->letter,
                    'reference_no' => $queue->reference_no,
                    'phone' => $queue->phone,
                    'email' => $queue->email,
                    'name' => $queue->name,
                    'position' => $queue->position,
                    'created_at' => $queue->created_at,
                    'updated_at' => $queue->updated_at,
                    'nik' => $queue->nik,
                    'status_queue' => $queue->status_queue,
                ]);
            }else{
                DB::table('queues_report')
                ->where('id', $queue->id)
                ->update([
                    'id' => $queue->id,
                    'service_id' => $queue->service_id,
                    'number' => $queue->number,
                    'called' => $queue->called,
                    'letter' => $queue->letter,
                    'reference_no' => $queue->reference_no,
                    'phone' => $queue->phone,
                    'email' => $queue->email,
                    'name' => $queue->name,
                    'position' => $queue->position,
                    'created_at' => $queue->created_at,
                    'updated_at' => $queue->updated_at,
                    'nik' => $queue->nik,
                    'status_queue' => $queue->status_queue,
                ]);
            }
        }
        $today = Carbon::now()->toDateString();
        $deleted = Queue::whereDate('created_at', $today)->delete();
    }
    
    public function insertCallsToReport()
    {
        $calls = Call::get();

        foreach ($calls as $call) {
            $existingRecord = DB::table('calls_report')
                ->where('id', $call->id)
                ->exists();

            if (!$existingRecord) {
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
            }else{                
                DB::table('calls_report')
                ->where('id', $call->id)
                ->update([
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
        }
        $today = Carbon::now()->toDateString();
        $deleted = Call::whereDate('created_at', $today)->delete();
    }
}

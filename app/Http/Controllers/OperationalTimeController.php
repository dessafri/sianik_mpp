<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OperationalTime;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Repositories\OperationalRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OperationalTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public $operational_time;

    public function __construct(OperationalRepository $operational_time)
    {
        $this->operational_time = $operational_time;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('operational_time.index', [
            'operational_times' => $this->operational_time->getAllOperational()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('operational_time.create', ['settings' => Setting::first()]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'on_time' => 'required',
            'off_time' => 'required',
            'break_time_start' => 'required',
            'break_time_finish' => 'required',
            'day' => 'required',
            'status' => 'required',
            'sound' => 'mimes:mp3'
        ]);
        // dd($request);
        DB::beginTransaction();
        try {
            $operational_time = $this->operational_time->create($request->all());
            Storage::put('public/operational_time_' . $operational_time->id . '_display.json', json_encode([]));
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something Went Wrong');
            return redirect()->route('operational_time.index');
        }
        DB::commit();
        $request->session()->flash('success', 'Succesfully inserted the record');
        return redirect()->route('operational_time.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(OperationalTime $operational_time)
    {
        return view('operational_time.edit', [
            'operational_time' => $operational_time,
            'settings' => Setting::first()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OperationalTime $operational_time)
    {
        $request->validate([
            'on_time' => 'required',
            'off_time' => 'required',
            'break_time_start' => 'required',
            'break_time_finish' => 'required',
            'day' => 'required',
            'status' => 'required',
            'sound' => 'mimes:mp3'
        ]);
        DB::beginTransaction();
        try {
            $operational_time = $this->operational_time->update($request->all(), $operational_time);
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something Went Wrong');
            return redirect()->route('operational_time.index');
        }
        DB::commit();
        $request->session()->flash('success', 'Succesfully updated the record');
        return redirect()->route('operational_time.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(OperationalTime $operational_time, Request $request)
    {

        DB::beginTransaction();
        try {
            $operational_time = $this->operational_time->delete($request->all(), $operational_time);
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something Went Wrong');
            return redirect()->route('operational_time.index');
        }
        DB::commit();
        $request->session()->flash('success', 'Succesfully deleted the record');
        return redirect()->route('operational_time.index');
    }
}

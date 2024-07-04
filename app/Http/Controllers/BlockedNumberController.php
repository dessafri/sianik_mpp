<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlockedNumber;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Repositories\BlockedNumberRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BlockedNumberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public $blocked_number;

    public function __construct(BlockedNumberRepository $blocked_number)
    {
        $this->blocked_number = $blocked_number;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        return view('blocked_number.index', [
            'blocked_numbers' => $this->blocked_number->getAllBlocked()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('blocked_number.create', ['settings' => Setting::first()]);
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
                'phone_number' => 'required',
            ]);

            $phoneNumber = $request->phone_number;
            if (substr($phoneNumber, 0, 1) === '0') {
                $phoneNumber = '62' . substr($phoneNumber, 1);
            }

            DB::beginTransaction();
            try {
                $blocked_number = $this->blocked_number->create(['phone_number' => $phoneNumber]);
                Storage::put('public/blocked_number_' . $blocked_number->id . '_display.json', json_encode([]));
            } catch (\Exception $e) {
                DB::rollback();
                $request->session()->flash('error', 'Something Went Wrong');
                return redirect()->route('blocked_number.index');
            }
            DB::commit();

            $request->session()->flash('success', 'Succesfully inserted the record');
            return redirect()->route('blocked_number.index');
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
    public function edit(BlockedNumber $blocked_number)
    {
        return view('blocked_number.edit', [
            'blocked_number' => $blocked_number,
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
    public function update(Request $request, BlockedNumber $blocked_number)
    {
        $request->validate([
            'phone_number' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $blocked_number = $this->blocked_number->update($request->all(), $blocked_number);
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something Went Wrong');
            return redirect()->route('blocked_number.index');
        }
        DB::commit();
        $request->session()->flash('success', 'Succesfully updated the record');
        return redirect()->route('blocked_number.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(BlockedNumber $blocked_number, Request $request)
    {

        DB::beginTransaction();
        try {
            $blocked_number = $this->blocked_number->delete($request->all(), $blocked_number);
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something Went Wrong');
            return redirect()->route('blocked_number.index');
        }
        DB::commit();
        $request->session()->flash('success', 'Succesfully deleted the record');
        return redirect()->route('blocked_number.index');
    }

    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach ($request->blocked_number as $id) {
                $blockedNumber = BlockedNumber::find($id);
                if ($blockedNumber) {
                    $blockedNumber->delete();
                }
            }
            DB::commit();
            $request->session()->flash('success', 'Successfully deleted the records');
        } catch (\Exception $e) {
            DB::rollback();
            $request->session()->flash('error', 'Something went wrong');
        }
        return redirect()->route('blocked_number.index');
    }

}

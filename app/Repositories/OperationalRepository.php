<?php

namespace App\Repositories;

use App\Models\OperationalTime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class OperationalRepository
{
    public function getAllOperational()
    {
        return OperationalTime::get();
    }

    public function getOperationalById($id)
    {
        return OperationalTime::find($id);
    }
    public function create($data)
    {
        $soundFile = $data['sound'];

        if ($soundFile instanceof UploadedFile && $soundFile->isValid()) {
            $path = $soundFile->store('sound', 'public');
        } else {
            return response()->json(['error' => 'Invalid sound file.'], 400);
        }

        $operational_time = OperationalTime::create([
            'on_time' => $data['on_time'],
            'off_time' => $data['off_time'],
            'break_time_start' => $data['break_time_start'],
            'break_time_finish' => $data['break_time_finish'],
            'day' => $data['day'],
            'sound' => $path,
            'status' => $data['status']
        ]);
        return $operational_time;
    }
    public function update($data, $operational_time)
    {
        if(isset($data['sound']) && $data['sound']->isValid())
        {
            //delete old file
            if($operational_time->sound)
            {
                Storage::disk('public')->delete($operational_time->sound);
            }
            //store new file
            $path = $data['sound']->store('sound','public');
            $operational_time->sound = $path;
        }
        $operational_time->on_time = $data['on_time'];
        $operational_time->off_time = $data['off_time'];
        $operational_time->break_time_start = $data['break_time_start'];
        $operational_time->break_time_finish = $data['break_time_finish'];
        $operational_time->day = $data['day'];
        $operational_time->status = $data['status'];
        $operational_time->save();
        return $operational_time;
    }
    public function delete($data, $operational_time)
    {
        Storage::delete('public/operational_time_' . $operational_time->id . '_display.json');
        $operational_time->delete();
    }
}

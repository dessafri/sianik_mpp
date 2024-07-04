<?php

namespace App\Repositories;

use App\Models\BlockedNumber;
use Illuminate\Support\Facades\Storage;

class BlockedNumberRepository
{
    public function getAllBlocked()
    {
        return BlockedNumber::get();
    }

    public function getBlockedById($id)
    {
        return BlockedNumber::find($id);
    }
    public function create($data)
    {
        $blocked_number = BlockedNumber::create([
            'phone_number' => $data['phone_number'],
        ]);
        return $blocked_number;
    }
    public function update($data, $blocked_number)
    {
        $blocked_number->phone_number = $data['phone_number'];
        $blocked_number->save();
        return $blocked_number;
    }
    public function delete($data, $blocked_number)
    {
        Storage::delete('public/blocked_number_' . $blocked_number->id . '_display.json');
        $blocked_number->delete();
    }
}

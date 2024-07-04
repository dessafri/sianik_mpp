<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','letter','start_number','status','sms_enabled','optin_message_enabled','call_message_enabled','noshow_message_enabled','completed_message_enabled','optin_message_format','call_message_format','noshow_message_format','completed_message_format','ask_name','name_required','ask_email','email_required','ask_phone','phone_required','status_message_enabled','status_message_format','status_message_positions','online_limit','offline_limit','status_online','ask_nik','combined_limit','limit'
    ];


    protected $casts = [
        'status_message_positions' => 'array',
    ];

    public function queues(){
        return $this->hasMany(Queue::class);
    }

    public function calls(){
        return $this->hasMany(Call::class,'service_id');
    }

    // public function getStatusPositionsAttribute(){
    //     if($this->status_message_positions){
    //         json_decode($this->status_message_positions, true);
    //     }
    //     else null;
    // }
}

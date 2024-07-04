<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedNumber extends Model
{
    use HasFactory;
    protected $table = 'blocked_numbers';

    protected $fillable = [
        'phone_number',
    ];
}

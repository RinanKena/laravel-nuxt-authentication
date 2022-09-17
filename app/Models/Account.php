<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $fillable = [
        'type',
        'value',
        'user_id',
        'activated',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationNumero extends Model
{
    protected $table = 'notification_numeros';

    public $timestamps = false;

    protected $fillable = [
        'telephone',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}

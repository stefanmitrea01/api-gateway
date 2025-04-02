<?php

// app/Models/RequestLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLogModel extends Model
{
    protected $table = 'request_logs';

    protected $guarded = [];

    protected $casts = [
        'request_headers' => 'array',
        'request_body' => 'array',
        'response_headers' => 'array',
        'response_body' => 'array',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    protected $fillable = [
        'filename',
        'original_name',
        'status',
        'rows_total',
        'rows_inserted',
        'rows_skipped',
        'error_message',
        'user_id',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    protected $fillable = [
        'status',
        'imported_at',
        'processed_files',
        'total_products',
        'error',
        'memory_usage',
        'execution_time',
    ];

    protected $casts = [
        'processed_files' => 'array',
        'imported_at' => 'datetime',
    ];
}

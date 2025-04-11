<?php

namespace App\Modules\TempoHours\Models;

use Illuminate\Database\Eloquent\Model;

class TempoWorklog extends Model
{
    protected $fillable = [
        'tempo_worklog_id',
        'issue_key',
        'project_key',
        'started_at',
        'time_spent_seconds',
        'billable_seconds',
        'author_account_id',
        'description',
        'is_invoice_ready',
        'last_synced_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'is_invoice_ready' => 'boolean',
    ];
} 
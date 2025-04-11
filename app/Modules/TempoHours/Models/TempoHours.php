<?php

namespace App\Modules\TempoHours\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TempoHours extends Model
{
    protected $fillable = [
        'project_key',
        'period',
        'period_hours',
        'invoice_ready_hours',
        'last_synced_at',
        'name',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'period_hours' => 'integer',
        'invoice_ready_hours' => 'integer',
    ];

    public function worklogs()
    {
        return $this->hasMany(TempoWorklog::class, 'project_key', 'project_key');
    }

    public function getNameAttribute()
    {
        if ($this->attributes['name']) {
            return $this->attributes['name'];
        }

        try {
            $response = Http::withBasicAuth(
                config('services.jira.email'),
                config('services.jira.api_token')
            )->get(config('services.jira.base_url') . "/rest/api/2/project/{$this->project_key}");

            if ($response->successful()) {
                $projectData = $response->json();
                $this->name = $projectData['name'];
                $this->save();
                return $projectData['name'];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching project name from Jira', [
                'project_key' => $this->project_key,
                'error' => $e->getMessage()
            ]);
        }

        return 'N/A';
    }
}

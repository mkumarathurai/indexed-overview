<?php

namespace App\Modules\TempoHours\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\TempoHours\Services\TempoApiService;
use App\Modules\TempoHours\Services\JiraApiService;
use App\Modules\TempoHours\Models\TempoHours;
use App\Modules\TempoHours\Models\TempoWorklog;
use Carbon\Carbon;

class SyncTempoHours extends Command
{
    protected $signature = 'tempo:sync {--period= : The period to sync (format: YYYY-MM)}';
    protected $description = 'Sync Tempo hours for a specific period';

    protected $tempoApiService;

    public function __construct(TempoApiService $tempoApiService)
    {
        parent::__construct();
        $this->tempoApiService = $tempoApiService;
    }

    public function handle()
    {
        $period = $this->option('period');
        
        if (!$period) {
            $period = now()->format('Y-m');
        }

        $this->info("Syncing Tempo hours for period: {$period}");

        try {
            $this->tempoApiService->syncWorklogs($period);
            $this->info('Tempo hours synced successfully!');
        } catch (\Exception $e) {
            $this->error('Error syncing Tempo hours: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

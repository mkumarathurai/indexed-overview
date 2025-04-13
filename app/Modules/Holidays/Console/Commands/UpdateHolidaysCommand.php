<?php

namespace App\Modules\Holidays\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Holidays\Services\HolidayService;
use Illuminate\Support\Facades\Log;

class UpdateHolidaysCommand extends Command
{
    protected $signature = 'holidays:update';
    protected $description = 'Update holiday information for all employees';

    public function handle(HolidayService $holidayService)
    {
        $this->info('Starting holiday update...');
        
        try {
            if ($holidayService->fetchAndUpdateHolidays()) {
                $this->info('Holiday information updated successfully!');
            } else {
                $this->error('Failed to update holiday information');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile());
            $this->error('Line: ' . $e->getLine());
            $this->error('Trace: ' . $e->getTraceAsString());
            Log::error('Holiday update failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
} 
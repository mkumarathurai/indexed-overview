<?php

namespace App\Modules\Holidays\Services;

use App\Modules\Holidays\Models\Holiday;
use App\Modules\Holidays\Models\HolidayWorklog;
use App\Modules\Employees\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    private const HOLIDAY_EPIC_KEY = 'INTERNAL-9';
    private const HOURS_PER_DAY = 7.5;

    public function fetchAndUpdateHolidays()
    {
        try {
            // Get all employees
            $employees = Employee::all();
            Log::info('Found ' . count($employees) . ' employees to process');
            
            foreach ($employees as $employee) {
                Log::info('Processing employee: ' . $employee->email);
                
                // Get worklogs for the employee from the holiday epic
                $worklogs = $this->getEmployeeHolidayWorklogs($employee->email);
                Log::info('Found ' . count($worklogs) . ' worklogs for employee ' . $employee->email);
                
                // Calculate total hours spent on holidays
                $totalHours = 0;
                foreach ($worklogs as $worklog) {
                    $totalHours += $worklog['timeSpentSeconds'] / 3600;
                }
                
                // Convert hours to days
                $usedDays = round($totalHours / self::HOURS_PER_DAY, 2);
                Log::info('Calculated ' . $usedDays . ' days used for employee ' . $employee->email);
                
                // Get or create holiday record for current year
                $holiday = Holiday::firstOrNew([
                    'employee_id' => $employee->id,
                    'year' => date('Y')
                ]);
                
                // Set default total days if not set
                if (!$holiday->total_days) {
                    $holiday->total_days = 25;
                }
                
                // Update holiday record
                $holiday->used_days = $usedDays;
                $holiday->remaining_days = $holiday->total_days - $usedDays;
                $holiday->last_updated = now();
                
                try {
                    $holiday->save();
                    Log::info('Successfully saved holiday record for employee ' . $employee->email);
                } catch (\Exception $e) {
                    Log::error('Failed to save holiday record for employee ' . $employee->email, [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }
            }
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update holidays', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function getEmployeeHolidayWorklogs(string $email)
    {
        try {
            Log::info('Fetching worklogs for: ' . $email);
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.tempo.api_token'),
                'Accept' => 'application/json'
            ])->get('https://api.eu.tempo.io/4/worklogs', [
                'from' => date('Y-01-01'),
                'to' => date('Y-12-31'),
                'worker' => $email
            ]);

            if (!$response->successful()) {
                Log::error('Failed to fetch worklogs', [
                    'email' => $email,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return [];
            }

            $data = $response->json();
            $worklogs = $data['results'] ?? [];
            
            Log::info('Raw worklogs for ' . $email . ': ' . json_encode($worklogs));

            // Filter worklogs for the holiday epic
            $filteredWorklogs = array_filter($worklogs, function ($worklog) {
                $isHoliday = isset($worklog['issue']['key']) && $worklog['issue']['key'] === self::HOLIDAY_EPIC_KEY;
                if ($isHoliday) {
                    Log::info('Found holiday worklog: ' . json_encode($worklog));
                }
                return $isHoliday;
            });

            Log::info('Found ' . count($filteredWorklogs) . ' holiday worklogs for ' . $email);
            return $filteredWorklogs;
        } catch (\Exception $e) {
            Log::error('Error fetching worklogs for ' . $email, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function createHolidayWorklog(array $data)
    {
        try {
            return HolidayWorklog::create([
                'employee_id' => $data['employee_id'],
                'date' => $data['date'],
                'hours' => $data['hours'],
                'description' => $data['description'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating holiday worklog', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    public function getAllHolidayWorklogs()
    {
        return HolidayWorklog::with('employee')
            ->orderBy('date', 'desc')
            ->get();
    }

    public function getHolidayWorklogsByEmployee($employeeId)
    {
        return HolidayWorklog::where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->get();
    }
}
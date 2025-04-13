<?php

namespace App\Modules\Employees\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Employees\Models\Employee;
use Illuminate\Support\Facades\Log;

class UpdateEmployeeInfo extends Command
{
    protected $signature = 'employees:update-info';
    protected $description = 'Update employee information for specific employees';

    public function handle()
    {
        $employees = [
            'mk@indexed.dk' => [
                'start_date' => '2022-09-19'
            ]
        ];

        foreach ($employees as $email => $data) {
            $employee = Employee::where('email', $email)->first();
            
            if ($employee) {
                // Only update start date, preserve all other data
                $employee->update([
                    'start_date' => $data['start_date']
                ]);
                $this->info("Updated start date for {$email} (Name: {$employee->name})");
                Log::info("Updated employee start date", [
                    'email' => $email,
                    'name' => $employee->name,
                    'start_date' => $data['start_date']
                ]);
            } else {
                $this->error("Employee not found: {$email}. Please run 'php artisan jira:sync-users' first.");
                Log::error("Employee not found", ['email' => $email]);
            }
        }

        $this->info('Employee start date update completed.');
    }
} 
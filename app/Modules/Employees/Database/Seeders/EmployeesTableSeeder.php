<?php

namespace App\Modules\Employees\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Employees\Models\Employee;

class EmployeesTableSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Mathi Kumarathurai',
                'email' => 'mk@indexed.dk',
                'title' => 'Software Developer',
                'status' => 'active',
                'type' => 'internal'
            ],
            [
                'name' => 'Test Employee',
                'email' => 'test@indexed.dk',
                'title' => 'Test Engineer',
                'status' => 'active',
                'type' => 'internal'
            ]
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
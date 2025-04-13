<?php

namespace App\Modules\Employees\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Employees\Models\Employee;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = [
            [
                'name' => 'Peter Sørensen',
                'title' => 'Indehaver',
                'email' => 'peter@indexed.dk',
                'work_phone' => '+45 28 43 42 39',
                'status' => 'active',
                'type' => 'employee',
            ],
            [
                'name' => 'Mathi Kumarathurai',
                'title' => 'Projektleder',
                'email' => 'mk@indexed.dk',
                'work_phone' => '+45 61 10 99 12',
                'private_phone' => '+45 27 89 00 91',
                'start_date' => Carbon::createFromFormat('d.m.Y', '19.09.2022'),
                'birthday' => Carbon::createFromFormat('d.m.Y', '23.07.1970'),
                'status' => 'active',
                'type' => 'employee',
            ],
            [
                'name' => 'Palle Houtved',
                'title' => 'Senior Webudvikler',
                'email' => 'pah@indexed.dk',
                'work_phone' => '+45 93 10 42 39',
                'private_phone' => '+45 22 16 96 10',
                'status' => 'active',
                'type' => 'employee',
            ],
            [
                'name' => 'Kristian Svalgaard',
                'title' => 'Senior Webudvikler',
                'email' => 'ks@indexed.dk',
                'work_phone' => '+45 51 91 98 38',
                'private_phone' => '+45 51 22 78 95',
                'status' => 'active',
                'type' => 'employee',
            ],
            [
                'name' => 'Morten Bak',
                'title' => 'Senior Webudvikler',
                'email' => 'meb@indexed.dk',
                'work_phone' => '+45 61 69 33 32',
                'private_phone' => '+45 51 22 78 95',
                'status' => 'active',
                'type' => 'employee',
            ],
        ];

        foreach ($employees as $employeeData) {
            Employee::updateOrCreate(
                ['email' => $employeeData['email']],
                $employeeData
            );
        }
    }
} 
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            \App\Modules\Employees\Database\Seeders\EmployeeSeeder::class,
            UserSeeder::class,
            HolidayWorklogSeeder::class,
        ]);
    }
}

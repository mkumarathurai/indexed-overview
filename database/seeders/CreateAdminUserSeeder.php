<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Mathi Kumarathurai',
            'email' => 'mk@indexed.dk',
            'password' => Hash::make('Thecloset@2307'),
        ]);
    }
}

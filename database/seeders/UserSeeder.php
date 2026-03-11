<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'first_name' => 'Pán',
                'last_name' => 'Admin',
                'email' => 'admin@ukf.sk',
                'password' => Hash::make('123'),
                'role' => 'admin',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Dávid',
                'last_name' => 'Držík',
                'email' => 'ddrzik@ukf.sk',
                'password' => Hash::make('456'),
                'role' => 'user',
                'premium_until' => now()->addDays(30),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Jozef',
                'last_name' => 'Kapusta',
                'email' => 'jkapusta@ukf.sk',
                'password' => Hash::make('789'),
                'role' => 'user',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Mária',
                'last_name' => 'Novotná',
                'email' => 'marta.novotna@ukf.sk',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'premium_until' => now()->addDays(60),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Peter',
                'last_name' => 'Varga',
                'email' => 'peter.varga@ukf.sk',
                'password' => Hash::make('pass456'),
                'role' => 'user',
                'premium_until' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Elena',
                'last_name' => 'Svobodová',
                'email' => 'elena.svobodova@ukf.sk',
                'password' => Hash::make('pass789'),
                'role' => 'user',
                'premium_until' => now()->addDays(15),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
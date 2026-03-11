<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        DB::table('categories')->insert([
            ['name' => 'Práca', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Škola', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Osobné', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nápady', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'TODO', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Finance', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Zdravie', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
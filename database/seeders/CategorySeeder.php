<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'name' => 'Práca',
            'color' => '#FF6B6B',
        ]);

        Category::create([
            'name' => 'Škola',
            'color' => '#4ECDC4',
        ]);

        Category::create([
            'name' => 'Osobné',
            'color' => '#FFE66D',
        ]);

        Category::create([
            'name' => 'Nápady',
            'color' => '#95E1D3',
        ]);

        Category::create([
            'name' => 'TODO',
            'color' => '#A8E6CF',
        ]);

        Category::create([
            'name' => 'Finance',
            'color' => '#F38181',
        ]);

        Category::create([
            'name' => 'Zdravie',
            'color' => '#AA96DA',
        ]);
    }
}
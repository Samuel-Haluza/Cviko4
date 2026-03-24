<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Komentáre k poznámkam
        DB::table('comments')->insert([
            // Comments for Note 1
            [
                'user_id' => 2,
                'note_id' => 1,
                'task_id' => null,
                'title' => 'Prvý komentár',
                'body' => 'Výborný tutoriál na seeder, pomohol mi veľa!',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 3,
                'note_id' => 1,
                'task_id' => null,
                'title' => 'Upozornenie',
                'body' => 'Nezabudni na validáciu údajov pri vkladaní!',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Comments for Note 2
            [
                'user_id' => 3,
                'note_id' => 2,
                'task_id' => null,
                'title' => 'Nakupovacia listina',
                'body' => 'Pridaj aj maslo a múka!',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Comments for Task 1
            [
                'user_id' => 2,
                'note_id' => null,
                'task_id' => 1,
                'title' => 'Progres',
                'body' => 'Už som sa naučil používať seeders a migrations.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'user_id' => 5,
                'note_id' => null,
                'task_id' => 1,
                'title' => 'Tip',
                'body' => 'Pozri sa na Laravel dokumentáciu - veľmi užitočná!',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // Comments for Task 2
            [
                'user_id' => 2,
                'note_id' => null,
                'task_id' => 2,
                'title' => 'Status',
                'body' => 'API endpoints sú už hotové. Testovanie nasleduje.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\Note;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Všetky poznámky
        $notes = Note::all();

        // Pre každú poznámku vytvoríme 2-5 úloh
        foreach ($notes as $note) {
            $taskCount = fake()->numberBetween(2, 5);
            Task::factory($taskCount)->create([
                'note_id' => $note->id,
            ]);
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Pridaj note_id a task_id
            $table->foreignId('note_id')
                ->nullable()
                ->after('user_id')
                ->constrained('notes')
                ->onDelete('cascade');

            $table->foreignId('task_id')
                ->nullable()
                ->after('note_id')
                ->constrained('tasks')
                ->onDelete('cascade');

            $table->index('note_id');
            $table->index('task_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('note_id');
            $table->dropConstrainedForeignId('task_id');
        });
    }
};

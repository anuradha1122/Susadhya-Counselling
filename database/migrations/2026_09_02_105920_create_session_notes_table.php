<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_notes', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('counselling_session_id');
            $table->foreignId('author_id');

            $table->string('note_type')->default('progress');
            $table->string('visibility')->default('private');
            $table->text('content');

            $table->timestamps();

            $table->index(['counselling_session_id', 'note_type'], 'session_notes_session_type_idx');
            $table->index(['author_id', 'created_at'], 'session_notes_author_created_idx');

            $table
                ->foreign('counselling_session_id', 'session_notes_session_fk')
                ->references('id')
                ->on('counselling_sessions')
                ->cascadeOnDelete();

            $table
                ->foreign('author_id', 'session_notes_author_fk')
                ->references('id')
                ->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_notes');
    }
};

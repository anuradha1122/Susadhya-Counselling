<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_screening_answers', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_intake_id');

            $table->string('question_key');
            $table->text('question_text');

            $table->unsignedTinyInteger('answer_score')->nullable();
            $table->string('answer_value')->nullable();
            $table->text('answer_notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['client_intake_id', 'question_key'],
                'csa_intake_question_unique'
            );

            $table->index('question_key', 'csa_question_key_idx');
            $table->index('answer_score', 'csa_answer_score_idx');

            $table
                ->foreign('client_intake_id', 'csa_intake_fk')
                ->references('id')
                ->on('client_intakes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_screening_answers');
    }
};

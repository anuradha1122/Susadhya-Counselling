<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'counsellor_profile_language',
            function (Blueprint $table) {
                $table->foreignId('counsellor_profile_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('language_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table->enum('proficiency', [
                    'basic',
                    'conversational',
                    'fluent',
                    'native',
                ])->default('conversational');

                $table->primary([
                    'counsellor_profile_id',
                    'language_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('counsellor_profile_language');
    }
};

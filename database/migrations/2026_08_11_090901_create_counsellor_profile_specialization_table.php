<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'counsellor_profile_specialization',
            function (Blueprint $table) {
                $table->foreignId('counsellor_profile_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('specialization_id')
                    ->constrained()
                    ->restrictOnDelete();

                $table->primary([
                    'counsellor_profile_id',
                    'specialization_id',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'counsellor_profile_specialization'
        );
    }
};

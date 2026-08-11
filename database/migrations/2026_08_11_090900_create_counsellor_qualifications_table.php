<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'counsellor_qualifications',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('counsellor_profile_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->string('qualification');
                $table->string('institution');
                $table->string('field_of_study')->nullable();

                $table->unsignedSmallInteger('year_completed')
                    ->nullable()
                    ->index();

                $table->string('certificate_number', 100)
                    ->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('counsellor_qualifications');
    }
};

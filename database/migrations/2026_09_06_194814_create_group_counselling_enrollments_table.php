<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_counselling_enrollments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('program_id')->constrained('group_counselling_programs', indexName: 'gce_program_fk')->cascadeOnDelete();
            $table->foreignId('client_profile_id')->constrained('client_profiles', indexName: 'gce_client_profile_fk')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'waitlisted', 'cancelled', 'completed'])->default('pending');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('client_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'client_profile_id'], 'gce_program_client_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_counselling_enrollments');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counsellor_blocked_slots', function (Blueprint $table): void {
            $table->id();

            $table
                ->foreignId('counsellor_profile_id')
                ->constrained('counsellor_profiles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('blocked_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            $table->boolean('is_full_day')->default(false);
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();

            $table
                ->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table
                ->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamps();

            $table->index(
                ['counsellor_profile_id', 'blocked_date'],
                'cbs_counsellor_date_idx'
            );

            $table->index(
                ['blocked_date', 'is_full_day'],
                'cbs_date_full_day_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counsellor_blocked_slots');
    }
};

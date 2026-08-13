<?php

use App\Models\CounsellorAvailabilityRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counsellor_availability_rules', function (Blueprint $table): void {
            $table->id();

            $table
                ->foreignId('counsellor_profile_id')
                ->constrained('counsellor_profiles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');

            $table->string('mode', 30)->default(CounsellorAvailabilityRule::MODE_BOTH);
            $table->unsignedSmallInteger('slot_duration_minutes')->default(60);
            $table->unsignedSmallInteger('buffer_minutes')->default(0);
            $table->unsignedSmallInteger('capacity_per_slot')->default(1);

            $table->string('timezone', 80)->default('Asia/Colombo');
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();

            $table->boolean('is_active')->default(true);
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
                ['counsellor_profile_id', 'day_of_week', 'is_active'],
                'car_counsellor_day_active_idx'
            );

            $table->index(
                ['day_of_week', 'start_time', 'end_time'],
                'car_day_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counsellor_availability_rules');
    }
};

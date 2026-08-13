<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counsellor_availability_breaks', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('counsellor_availability_rule_id');

            $table->string('title')->default('Break');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);

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

            $table->foreign(
                'counsellor_availability_rule_id',
                'cab_rule_fk'
            )
                ->references('id')
                ->on('counsellor_availability_rules')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->index(
                ['counsellor_availability_rule_id', 'is_active'],
                'cab_rule_active_idx'
            );

            $table->index(
                ['start_time', 'end_time'],
                'cab_time_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counsellor_availability_breaks');
    }
};

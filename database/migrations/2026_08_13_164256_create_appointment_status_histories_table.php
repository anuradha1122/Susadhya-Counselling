<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_status_histories', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('appointment_id');

            $table->string('from_status', 40)->nullable();
            $table->string('to_status', 40);
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();

            $table->unsignedBigInteger('changed_by')->nullable();

            $table->timestamps();

            $table
                ->foreign('appointment_id', 'ash_appointment_fk')
                ->references('id')
                ->on('appointments')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table
                ->foreign('changed_by', 'ash_changed_by_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->index(
                ['appointment_id', 'created_at'],
                'ash_appointment_created_idx'
            );

            $table->index(
                ['to_status', 'created_at'],
                'ash_status_created_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_status_histories');
    }
};

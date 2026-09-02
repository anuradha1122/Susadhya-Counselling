<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'clinical_note_versions',
            function (Blueprint $table): void {
                $table->id();

                $table->foreignId('clinical_note_id')
                    ->constrained('clinical_notes')
                    ->cascadeOnDelete();

                $table->unsignedInteger('version');

                $table->string('title')->nullable();

                $table->text('note');
                $table->text('formulation')->nullable();
                $table->text('intervention')->nullable();
                $table->text('risk_assessment')->nullable();
                $table->text('plan')->nullable();

                $table->string('risk_level', 20);
                $table->string('status', 20);

                $table->foreignId('changed_by')
                    ->constrained('users')
                    ->restrictOnDelete();

                $table->string(
                    'change_reason',
                    1000,
                )->nullable();

                $table->timestamp('signed_at')->nullable();
                $table->timestamp('locked_at')->nullable();

                $table->timestamps();

                $table->unique([
                    'clinical_note_id',
                    'version',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_note_versions');
    }
};

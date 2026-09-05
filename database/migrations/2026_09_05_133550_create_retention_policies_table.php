<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'retention_policies',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid('uuid')
                    ->unique('rp_uuid_uq');

                $table->string('category', 80)
                    ->unique('rp_category_uq');

                $table->string('name', 160);

                $table->text('description')
                    ->nullable();

                $table->unsignedInteger(
                    'retention_days'
                )->nullable();

                $table->string('action', 32)
                    ->default('review');

                $table->boolean('enabled')
                    ->default(false);

                $table->boolean(
                    'automatic_execution'
                )->default(false);

                $table->text('legal_basis')
                    ->nullable();

                $table->unsignedBigInteger(
                    'updated_by'
                )->nullable();

                $table->timestamp('reviewed_at')
                    ->nullable();

                $table->timestamps();

                $table->foreign(
                    'updated_by',
                    'rp_updated_by_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'retention_policies'
        );
    }
};

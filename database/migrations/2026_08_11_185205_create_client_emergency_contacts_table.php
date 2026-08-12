<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_emergency_contacts', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('client_profile_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 150);
            $table->string('relationship', 120);
            $table->string('phone', 30);
            $table->string('alternate_phone', 30)->nullable();
            $table->string('email')->nullable();

            $table->boolean('may_contact_in_emergency')
                ->default(false);

            $table->boolean('is_primary')
                ->default(true);

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index([
                'client_profile_id',
                'is_primary',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_emergency_contacts');
    }
};

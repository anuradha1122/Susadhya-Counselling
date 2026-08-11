<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counselling_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes');
            $table->enum('service_mode', [
                'online',
                'in_person',
                'both',
            ]);
            $table->enum('target_age_group', [
                'children',
                'adolescents',
                'adults',
                'seniors',
                'all_ages',
                'custom',
            ])->default('all_ages');
            $table->unsignedTinyInteger('minimum_age')->nullable();
            $table->unsignedTinyInteger('maximum_age')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->char('currency', 3)->default('LKR');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->enum('status', [
                'active',
                'inactive',
                'archived',
            ])->default('active');
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();

            $table->index([
                'service_category_id',
                'status',
            ]);
            $table->index([
                'service_mode',
                'target_age_group',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselling_services');
    }
};

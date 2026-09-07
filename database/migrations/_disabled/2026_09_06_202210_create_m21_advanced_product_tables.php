<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_counselling_programs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('counselling_service_id')->nullable()->constrained('counselling_services')->nullOnDelete();
            $table->foreignId('lead_counsellor_id')->nullable()->constrained('counsellors')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('mode', ['online', 'in_person', 'hybrid'])->default('online');
            $table->string('location')->nullable();
            $table->unsignedSmallInteger('capacity')->default(10);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('is_active')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('group_counselling_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('program_id')->constrained('group_counselling_programs', indexName: 'gcs_program_fk')->cascadeOnDelete();
            $table->foreignId('counsellor_id')->nullable()->constrained('counsellors', indexName: 'gcs_counsellor_fk')->nullOnDelete();
            $table->string('title');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->string('meeting_url')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });

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

        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sessions_count')->default(1);
            $table->unsignedSmallInteger('validity_days')->default(30);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_subscription')->default(false);
            $table->boolean('is_active')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('service_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages', indexName: 'spi_package_fk')->cascadeOnDelete();
            $table->foreignId('counselling_service_id')->constrained('counselling_services', indexName: 'spi_service_fk')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_package_id', 'counselling_service_id'], 'spi_package_service_unique');
        });

        Schema::create('client_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('client_profile_id')->constrained('client_profiles', indexName: 'cs_client_profile_fk')->cascadeOnDelete();
            $table->foreignId('service_package_id')->constrained('service_packages', indexName: 'cs_package_fk')->restrictOnDelete();
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->date('starts_on');
            $table->date('expires_on')->nullable();
            $table->unsignedSmallInteger('total_sessions')->default(0);
            $table->unsignedSmallInteger('used_sessions')->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_ai_summaries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('created_by')->nullable()->constrained('users', indexName: 'aas_created_by_fk')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'aas_reviewed_by_fk')->nullOnDelete();
            $table->string('source_type');
            $table->string('source_label');
            $table->string('title');
            $table->longText('source_text')->nullable();
            $table->longText('summary');
            $table->json('risk_flags')->nullable();
            $table->enum('status', ['draft', 'reviewed', 'archived'])->default('draft');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('translatable_type');
            $table->unsignedBigInteger('translatable_id');
            $table->string('locale', 10);
            $table->string('field');
            $table->longText('value');
            $table->foreignId('reviewed_by')->nullable()->constrained('users', indexName: 'ct_reviewed_by_fk')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['translatable_type', 'translatable_id'], 'ct_translatable_index');
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'ct_unique_translation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_translations');
        Schema::dropIfExists('admin_ai_summaries');
        Schema::dropIfExists('client_subscriptions');
        Schema::dropIfExists('service_package_items');
        Schema::dropIfExists('service_packages');
        Schema::dropIfExists('group_counselling_enrollments');
        Schema::dropIfExists('group_counselling_sessions');
        Schema::dropIfExists('group_counselling_programs');
    }
};

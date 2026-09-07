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
            $table->foreignId('lead_counsellor_profile_id')->nullable()->constrained('counsellor_profiles', indexName: 'gcp_lead_counsellor_profile_fk')->nullOnDelete();
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
    }

    public function down(): void
    {
        Schema::dropIfExists('group_counselling_programs');
    }
};

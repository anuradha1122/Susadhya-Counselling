<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('client_subscriptions');
    }
};

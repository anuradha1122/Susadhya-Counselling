<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_ai_summaries');
    }
};

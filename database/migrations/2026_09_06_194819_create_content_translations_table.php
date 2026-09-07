<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }
};

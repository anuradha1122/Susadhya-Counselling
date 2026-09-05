<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'content_snippets',
            function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique('content_snip_uuid_unique');

                $table->string('key', 150)->unique('content_snip_key_unique');
                $table->string('title', 180);

                $table->text('body');

                $table->string('placement', 80)
                    ->default('admin_operations');

                $table->string('status', 30)
                    ->default('draft');

                $table->timestamp('published_at')->nullable();

                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();

                $table
                    ->foreign('created_by', 'content_snip_created_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign('updated_by', 'content_snip_updated_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    ['placement', 'status'],
                    'content_snip_place_status_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('content_snippets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_faqs', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')->unique(
                'cf_uuid_uk'
            );

            $table->string(
                'category',
                100
            )->nullable();

            $table->string(
                'question',
                500
            );

            $table->longText(
                'answer'
            );

            $table->unsignedSmallInteger(
                'display_order'
            )->default(0);

            $table->boolean(
                'is_active'
            )->default(true);

            $table->timestamp(
                'published_at'
            )->nullable();

            $table->unsignedBigInteger(
                'created_by'
            )->nullable();

            $table->unsignedBigInteger(
                'updated_by'
            )->nullable();

            $table->timestamps();

            $table->index(
                [
                    'is_active',
                    'display_order',
                ],
                'cf_active_order_idx'
            );

            $table->foreign(
                'created_by',
                'cf_created_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'updated_by',
                'cf_updated_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cms_faqs'
        );
    }
};

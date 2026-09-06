<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_sections', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')->unique(
                'cs_uuid_uk'
            );

            $table->unsignedBigInteger(
                'cms_page_id'
            );

            $table->string(
                'key',
                80
            )->nullable();

            $table->string(
                'type',
                50
            );

            $table->string(
                'heading',
                220
            )->nullable();

            $table->text(
                'subheading'
            )->nullable();

            $table->json(
                'content'
            )->nullable();

            $table->json(
                'settings'
            )->nullable();

            $table->unsignedSmallInteger(
                'display_order'
            )->default(0);

            $table->boolean(
                'is_active'
            )->default(true);

            $table->unsignedBigInteger(
                'created_by'
            )->nullable();

            $table->unsignedBigInteger(
                'updated_by'
            )->nullable();

            $table->timestamps();

            $table->unique(
                [
                    'cms_page_id',
                    'key',
                ],
                'cs_page_key_uk'
            );

            $table->index(
                [
                    'cms_page_id',
                    'is_active',
                    'display_order',
                ],
                'cs_page_active_order_idx'
            );

            $table->foreign(
                'cms_page_id',
                'cs_page_fk'
            )
                ->references('id')
                ->on('cms_pages')
                ->cascadeOnDelete();

            $table->foreign(
                'created_by',
                'cs_created_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'updated_by',
                'cs_updated_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cms_sections'
        );
    }
};

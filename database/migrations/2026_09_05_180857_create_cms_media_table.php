<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_media', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')->unique(
                'cm_uuid_uk'
            );

            $table->string(
                'disk',
                40
            )->default('public');

            $table->string(
                'path',
                500
            )->unique(
                'cm_path_uk'
            );

            $table->string(
                'original_name',
                255
            );

            $table->string(
                'mime_type',
                100
            );

            $table->unsignedBigInteger(
                'size_bytes'
            );

            $table->string(
                'checksum',
                64
            )->nullable();

            $table->string(
                'alt_text',
                255
            )->nullable();

            $table->unsignedInteger(
                'width'
            )->nullable();

            $table->unsignedInteger(
                'height'
            )->nullable();

            $table->boolean(
                'is_active'
            )->default(true);

            $table->unsignedBigInteger(
                'uploaded_by'
            )->nullable();

            $table->timestamps();

            $table->index(
                [
                    'is_active',
                    'created_at',
                ],
                'cm_active_created_idx'
            );

            $table->foreign(
                'uploaded_by',
                'cm_uploaded_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cms_media'
        );
    }
};

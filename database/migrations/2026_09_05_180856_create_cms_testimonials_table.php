<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'cms_testimonials',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'uuid'
                )->unique(
                    'ct_uuid_uk'
                );

                $table->string(
                    'display_name',
                    120
                );

                $table->string(
                    'role_label',
                    160
                )->nullable();

                $table->text(
                    'quote'
                );

                $table->unsignedTinyInteger(
                    'rating'
                )->nullable();

                $table->string(
                    'image_path',
                    500
                )->nullable();

                $table->boolean(
                    'is_featured'
                )->default(false);

                $table->boolean(
                    'is_active'
                )->default(false);

                $table->unsignedSmallInteger(
                    'display_order'
                )->default(0);

                $table->boolean(
                    'consent_confirmed'
                )->default(false);

                $table->timestamp(
                    'consent_confirmed_at'
                )->nullable();

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
                        'is_featured',
                        'display_order',
                    ],
                    'ct_public_idx'
                );

                $table->foreign(
                    'created_by',
                    'ct_created_by_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->foreign(
                    'updated_by',
                    'ct_updated_by_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cms_testimonials'
        );
    }
};

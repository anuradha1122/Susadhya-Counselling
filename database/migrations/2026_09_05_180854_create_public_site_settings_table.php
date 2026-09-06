<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'public_site_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->uuid(
                    'uuid'
                )->unique(
                    'pss_uuid_uk'
                );

                $table->string(
                    'site_name',
                    160
                )->default(
                    'Susadhya Counselling'
                );

                $table->string(
                    'tagline',
                    255
                )->nullable();

                $table->string(
                    'logo_path',
                    500
                )->nullable();

                $table->string(
                    'favicon_path',
                    500
                )->nullable();

                $table->string(
                    'contact_email',
                    190
                )->nullable();

                $table->string(
                    'contact_phone',
                    50
                )->nullable();

                $table->string(
                    'whatsapp_number',
                    50
                )->nullable();

                $table->text(
                    'address'
                )->nullable();

                $table->text(
                    'office_hours'
                )->nullable();

                $table->json(
                    'social_links'
                )->nullable();

                $table->string(
                    'default_meta_title',
                    180
                )->nullable();

                $table->string(
                    'default_meta_description',
                    320
                )->nullable();

                $table->string(
                    'default_og_image_path',
                    500
                )->nullable();

                $table->text(
                    'footer_text'
                )->nullable();

                $table->text(
                    'emergency_notice'
                )->nullable();

                $table->string(
                    'booking_cta_label',
                    100
                )->default(
                    'Find a Counsellor'
                );

                $table->string(
                    'booking_cta_url',
                    500
                )->nullable();

                $table->unsignedBigInteger(
                    'created_by'
                )->nullable();

                $table->unsignedBigInteger(
                    'updated_by'
                )->nullable();

                $table->timestamps();

                $table->foreign(
                    'created_by',
                    'pss_created_by_fk'
                )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->foreign(
                    'updated_by',
                    'pss_updated_by_fk'
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
            'public_site_settings'
        );
    }
};

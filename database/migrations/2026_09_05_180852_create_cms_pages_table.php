<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_pages', function (Blueprint $table): void {
            $table->id();

            $table->uuid('uuid')->unique(
                'cp_uuid_uk'
            );

            $table->string(
                'title',
                180
            );

            $table->string(
                'slug',
                180
            )->unique(
                'cp_slug_uk'
            );

            $table->string(
                'menu_label',
                100
            )->nullable();

            $table->text(
                'excerpt'
            )->nullable();

            $table->longText(
                'body'
            )->nullable();

            $table->string(
                'template',
                40
            )->default('standard');

            $table->string(
                'status',
                20
            )->default('draft');

            $table->boolean(
                'show_in_header'
            )->default(false);

            $table->boolean(
                'show_in_footer'
            )->default(false);

            $table->unsignedSmallInteger(
                'menu_order'
            )->default(0);

            $table->string(
                'meta_title',
                180
            )->nullable();

            $table->string(
                'meta_description',
                320
            )->nullable();

            $table->string(
                'canonical_url',
                500
            )->nullable();

            $table->string(
                'og_title',
                180
            )->nullable();

            $table->string(
                'og_description',
                320
            )->nullable();

            $table->string(
                'og_image_path',
                500
            )->nullable();

            $table->boolean(
                'robots_index'
            )->default(true);

            $table->boolean(
                'robots_follow'
            )->default(true);

            $table->timestamp(
                'published_at'
            )->nullable();

            $table->unsignedBigInteger(
                'published_by'
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
                    'status',
                    'published_at',
                ],
                'cp_status_pub_idx'
            );

            $table->index(
                [
                    'show_in_header',
                    'menu_order',
                ],
                'cp_header_idx'
            );

            $table->index(
                [
                    'show_in_footer',
                    'menu_order',
                ],
                'cp_footer_idx'
            );

            $table->foreign(
                'published_by',
                'cp_published_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'created_by',
                'cp_created_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign(
                'updated_by',
                'cp_updated_by_fk'
            )
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'cms_pages'
        );
    }
};

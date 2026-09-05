<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'system_settings',
            function (Blueprint $table): void {
                $table->id();

                $table->string('group', 80);
                $table->string('key', 150)->unique('sys_setting_key_unique');

                $table->string('label', 150);
                $table->text('description')->nullable();

                $table->string('type', 30)->default('text');
                $table->text('value')->nullable();
                $table->json('options')->nullable();

                $table->boolean('is_public')->default(false);

                $table->unsignedBigInteger('updated_by')->nullable();

                $table->timestamps();

                $table
                    ->foreign('updated_by', 'sys_setting_updated_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    ['group', 'key'],
                    'sys_setting_group_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};

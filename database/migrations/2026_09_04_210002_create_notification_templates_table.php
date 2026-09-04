<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();

            $table->string('key', 150)->unique();

            $table->string('name');

            $table->string('subject')->nullable();

            $table->text('in_app_body');

            $table->text('email_body')->nullable();

            $table->string('sms_body', 500)->nullable();

            $table->json('variables')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};

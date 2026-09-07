<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages', indexName: 'spi_package_fk')->cascadeOnDelete();
            $table->foreignId('counselling_service_id')->constrained('counselling_services', indexName: 'spi_service_fk')->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_package_id', 'counselling_service_id'], 'spi_package_service_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_package_items');
    }
};

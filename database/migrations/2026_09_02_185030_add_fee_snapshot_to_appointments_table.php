<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'appointments',
            function (Blueprint $table): void {
                $table->decimal(
                    'fee_amount',
                    12,
                    2
                )->nullable();

                $table->char(
                    'fee_currency',
                    3
                )->nullable();

                $table->index(
                    [
                        'fee_currency',
                        'fee_amount',
                    ],
                    'appt_fee_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'appointments',
            function (Blueprint $table): void {
                $table->dropIndex(
                    'appt_fee_idx'
                );

                $table->dropColumn([
                    'fee_amount',
                    'fee_currency',
                ]);
            }
        );
    }
};

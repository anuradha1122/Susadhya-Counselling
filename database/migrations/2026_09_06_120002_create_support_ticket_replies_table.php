<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'support_ticket_replies',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique('str_uuid_uk');

                $table->unsignedBigInteger(
                    'support_ticket_id'
                );

                /*
                 * Null is retained for architectural flexibility,
                 * although normal M20 replies are authenticated.
                 */
                $table
                    ->unsignedBigInteger(
                        'user_id'
                    )
                    ->nullable();

                $table->text('body');

                /*
                 * Internal notes are never exposed to clients.
                 */
                $table
                    ->boolean('is_internal')
                    ->default(false);

                $table->timestamps();

                $table
                    ->foreign(
                        'support_ticket_id',
                        'str_ticket_fk'
                    )
                    ->references('id')
                    ->on('support_tickets')
                    ->cascadeOnDelete();

                $table
                    ->foreign(
                        'user_id',
                        'str_user_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    [
                        'support_ticket_id',
                        'created_at',
                    ],
                    'str_ticket_date_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'support_ticket_replies'
        );
    }
};

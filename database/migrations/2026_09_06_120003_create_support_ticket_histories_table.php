<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'support_ticket_histories',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique('sth_uuid_uk');

                $table->unsignedBigInteger(
                    'support_ticket_id'
                );

                $table
                    ->unsignedBigInteger(
                        'actor_id'
                    )
                    ->nullable();

                $table->string(
                    'event',
                    80
                );

                $table
                    ->string(
                        'from_status',
                        40
                    )
                    ->nullable();

                $table
                    ->string(
                        'to_status',
                        40
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'from_owner_id'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'to_owner_id'
                    )
                    ->nullable();

                $table
                    ->string(
                        'from_priority',
                        20
                    )
                    ->nullable();

                $table
                    ->string(
                        'to_priority',
                        20
                    )
                    ->nullable();

                /*
                 * Administrative resolution/routing explanation.
                 *
                 * Never copied into the central audit metadata.
                 */
                $table
                    ->text('note')
                    ->nullable();

                $table
                    ->json('metadata')
                    ->nullable();

                $table->timestamp(
                    'created_at'
                );

                $table
                    ->foreign(
                        'support_ticket_id',
                        'sth_ticket_fk'
                    )
                    ->references('id')
                    ->on('support_tickets')
                    ->cascadeOnDelete();

                $table
                    ->foreign(
                        'actor_id',
                        'sth_actor_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign(
                        'from_owner_id',
                        'sth_from_owner_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign(
                        'to_owner_id',
                        'sth_to_owner_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    [
                        'support_ticket_id',
                        'created_at',
                    ],
                    'sth_ticket_date_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'support_ticket_histories'
        );
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'support_tickets',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique('st_uuid_uk');

                /*
                 * Registered requester.
                 *
                 * Null for public/guest contact submissions.
                 */
                $table
                    ->unsignedBigInteger('requester_id')
                    ->nullable();

                /*
                 * Guest contact identity.
                 *
                 * These remain null for authenticated client tickets.
                 */
                $table
                    ->string('guest_name', 150)
                    ->nullable();

                $table
                    ->string('guest_email', 255)
                    ->nullable();

                $table->string(
                    'category',
                    40
                );

                $table->string(
                    'subject',
                    200
                );

                /*
                 * Operational support text only.
                 *
                 * Users are explicitly warned not to place
                 * counselling notes or emergency disclosures here.
                 */
                $table->text(
                    'description'
                );

                $table
                    ->string(
                        'priority',
                        20
                    )
                    ->default('normal');

                $table
                    ->string(
                        'status',
                        40
                    )
                    ->default('open');

                /*
                 * Admin/super-admin responsible for the ticket.
                 */
                $table
                    ->unsignedBigInteger(
                        'owner_id'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'resolved_at'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'closed_at'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'last_activity_at'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'created_by'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'updated_by'
                    )
                    ->nullable();

                $table->timestamps();

                $table
                    ->foreign(
                        'requester_id',
                        'st_requester_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign(
                        'owner_id',
                        'st_owner_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign(
                        'created_by',
                        'st_creator_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table
                    ->foreign(
                        'updated_by',
                        'st_updater_fk'
                    )
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();

                $table->index(
                    'requester_id',
                    'st_requester_idx'
                );

                $table->index(
                    'owner_id',
                    'st_owner_idx'
                );

                $table->index(
                    'status',
                    'st_status_idx'
                );

                $table->index(
                    'category',
                    'st_category_idx'
                );

                $table->index(
                    'priority',
                    'st_priority_idx'
                );

                $table->index(
                    'last_activity_at',
                    'st_activity_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'support_tickets'
        );
    }
};

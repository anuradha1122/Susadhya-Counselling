<?php

use App\Models\SecureDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'secure_documents',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->uuid('uuid')
                    ->unique(
                        'secure_documents_uuid_unique'
                    );

                $table
                    ->foreignId(
                        'client_profile_id'
                    )
                    ->constrained(
                        'client_profiles'
                    )
                    ->restrictOnDelete();

                $table
                    ->foreignId(
                        'client_case_id'
                    )
                    ->nullable()
                    ->constrained(
                        'client_cases'
                    )
                    ->nullOnDelete();

                $table
                    ->foreignId(
                        'counselling_session_id'
                    )
                    ->nullable()
                    ->constrained(
                        'counselling_sessions'
                    )
                    ->nullOnDelete();

                $table
                    ->foreignId(
                        'uploaded_by'
                    )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string(
                    'category',
                    40
                );

                $table->string(
                    'access_scope',
                    40
                );

                $table->string(
                    'title',
                    160
                );

                $table->string(
                    'original_name'
                );

                $table->string(
                    'stored_name'
                );

                $table
                    ->string(
                        'disk',
                        50
                    )
                    ->default('local');

                $table->string(
                    'path',
                    500
                );

                $table->string(
                    'mime_type',
                    150
                );

                $table->string(
                    'extension',
                    20
                );

                $table
                    ->unsignedBigInteger(
                        'size_bytes'
                    );

                $table->char(
                    'sha256',
                    64
                );

                $table
                    ->string(
                        'scan_status',
                        30
                    )
                    ->default(
                        SecureDocument::SCAN_PENDING
                    );

                $table
                    ->string(
                        'scanner',
                        80
                    )
                    ->nullable();

                $table
                    ->text(
                        'scan_message'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'scanned_at'
                    )
                    ->nullable();

                $table
                    ->timestamp(
                        'quarantined_at'
                    )
                    ->nullable();

                $table
                    ->json('metadata')
                    ->nullable();

                $table
                    ->foreignId(
                        'deleted_by'
                    )
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->timestamps();
                $table->softDeletes();

                $table->index(
                    [
                        'client_profile_id',
                        'category',
                    ],
                    'secure_documents_client_category_idx'
                );

                $table->index(
                    [
                        'client_case_id',
                        'category',
                    ],
                    'secure_documents_case_category_idx'
                );

                $table->index(
                    [
                        'access_scope',
                        'scan_status',
                    ],
                    'secure_documents_scope_scan_idx'
                );

                $table->index(
                    [
                        'uploaded_by',
                        'created_at',
                    ],
                    'secure_documents_uploader_date_idx'
                );

                $table->index(
                    'sha256',
                    'secure_documents_sha256_idx'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'secure_documents'
        );
    }
};

<?php

namespace Database\Factories;

use App\Models\ClientProfile;
use App\Models\SecureDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SecureDocument>
 */
class SecureDocumentFactory extends Factory
{
    protected $model =
        SecureDocument::class;

    public function definition(): array
    {
        $storedName =
            Str::uuid()
                ->toString()
            .'.pdf';

        return [
            'client_profile_id' => ClientProfile::factory(),

            'client_case_id' => null,

            'counselling_session_id' => null,

            'uploaded_by' => User::factory(),

            'category' => SecureDocument::CATEGORY_OTHER,

            'access_scope' => SecureDocument::SCOPE_CLIENT,

            'title' => fake()->sentence(3),

            'original_name' => 'document.pdf',

            'stored_name' => $storedName,

            'disk' => 'local',

            'path' => 'documents/private/test/'
                .$storedName,

            'mime_type' => 'application/pdf',

            'extension' => 'pdf',

            'size_bytes' => 1024,

            'sha256' => hash(
                'sha256',
                fake()->uuid()
            ),

            'scan_status' => SecureDocument::SCAN_CLEAN,

            'scanner' => 'test',

            'scan_message' => 'Clean test document.',

            'scanned_at' => now(),

            'quarantined_at' => null,

            'metadata' => null,

            'deleted_by' => null,
        ];
    }
}

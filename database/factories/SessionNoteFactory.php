<?php

namespace Database\Factories;

use App\Models\CounsellingSession;
use App\Models\SessionNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionNote>
 */
class SessionNoteFactory extends Factory
{
    protected $model = SessionNote::class;

    public function definition(): array
    {
        return [
            'counselling_session_id' => CounsellingSession::factory(),
            'author_id' => User::factory(),
            'note_type' => SessionNote::TYPE_PROGRESS,
            'visibility' => SessionNote::VISIBILITY_PRIVATE,
            'content' => fake()->paragraph(),
        ];
    }
}

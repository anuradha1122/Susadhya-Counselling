<?php

namespace Database\Factories;

use App\Models\CmsMedia;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CmsMedia>
 */
class CmsMediaFactory extends Factory
{
    protected $model =
        CmsMedia::class;

    public function definition(): array
    {
        $name =
            Str::uuid().'.jpg';

        return [
            'disk' => 'public',

            'path' => "cms/{$name}",

            'original_name' => 'image.jpg',

            'mime_type' => 'image/jpeg',

            'size_bytes' => 102400,

            'checksum' => hash(
                'sha256',
                $name
            ),

            'alt_text' => fake()->sentence(4),

            'width' => 1200,

            'height' => 800,

            'is_active' => true,
        ];
    }
}

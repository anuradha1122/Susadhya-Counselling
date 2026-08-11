<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Specialization;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CounsellorReferenceSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            'Anxiety and Stress Management',
            'Child and Adolescent Counselling',
            'Career Counselling',
            'Couples Counselling',
            'Family Counselling',
            'Grief and Bereavement',
            'Trauma Counselling',
            'Addiction Counselling',
            'Educational Counselling',
            'Workplace Counselling',
        ];

        foreach ($specializations as $name) {
            Specialization::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'is_active' => true,
                ]
            );
        }

        $languages = [
            ['name' => 'Sinhala', 'code' => 'si'],
            ['name' => 'Tamil', 'code' => 'ta'],
            ['name' => 'English', 'code' => 'en'],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['code' => $language['code']],
                [
                    'name' => $language['name'],
                    'is_active' => true,
                ]
            );
        }
    }
}

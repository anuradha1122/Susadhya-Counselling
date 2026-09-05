<?php

namespace App\Services\AdminOperations;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SystemSettingService
{
    public function all(): array
    {
        return SystemSetting::query()
            ->orderBy('group')
            ->orderBy('label')
            ->get()
            ->map(fn (SystemSetting $setting): array => [
                'id' => $setting->id,
                'group' => $setting->group,
                'key' => $setting->key,
                'label' => $setting->label,
                'description' => $setting->description,
                'type' => $setting->type,
                'value' => $setting->value,
                'options' => $setting->options ?? [],
                'is_public' => $setting->is_public,
            ])
            ->values()
            ->all();
    }

    public function updateMany(
        array $values,
        User $user
    ): void {
        $settings = SystemSetting::query()
            ->whereIn(
                'key',
                array_keys($values)
            )
            ->get()
            ->keyBy('key');

        foreach ($values as $key => $value) {
            $setting = $settings->get($key);

            if (! $setting) {
                throw ValidationException::withMessages([
                    "settings.{$key}" => 'Unknown system setting.',
                ]);
            }

            $setting->forceFill([
                'value' => $this->normalizeValue(
                    $setting,
                    $value
                ),
                'updated_by' => $user->id,
            ])->save();
        }
    }

    private function normalizeValue(
        SystemSetting $setting,
        mixed $value
    ): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        return match ($setting->type) {
            SystemSetting::TYPE_BOOLEAN => $this->booleanValue(
                $setting->key,
                $value
            ),

            SystemSetting::TYPE_INTEGER => $this->integerValue(
                $setting->key,
                $value
            ),

            SystemSetting::TYPE_EMAIL => $this->emailValue(
                $setting->key,
                $value
            ),

            SystemSetting::TYPE_URL => $this->urlValue(
                $setting->key,
                $value
            ),

            default => (string) $value,
        };
    }

    private function booleanValue(
        string $key,
        mixed $value
    ): string {
        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        if ($parsed === null) {
            throw ValidationException::withMessages([
                "settings.{$key}" => 'The value must be true or false.',
            ]);
        }

        return $parsed ? '1' : '0';
    }

    private function integerValue(
        string $key,
        mixed $value
    ): string {
        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        if ($parsed === false) {
            throw ValidationException::withMessages([
                "settings.{$key}" => 'The value must be an integer.',
            ]);
        }

        return (string) $parsed;
    }

    private function emailValue(
        string $key,
        mixed $value
    ): string {
        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_EMAIL
        );

        if ($parsed === false) {
            throw ValidationException::withMessages([
                "settings.{$key}" => 'The value must be a valid email address.',
            ]);
        }

        return $parsed;
    }

    private function urlValue(
        string $key,
        mixed $value
    ): string {
        $parsed = filter_var(
            $value,
            FILTER_VALIDATE_URL
        );

        if ($parsed === false) {
            throw ValidationException::withMessages([
                "settings.{$key}" => 'The value must be a valid URL.',
            ]);
        }

        return $parsed;
    }
}

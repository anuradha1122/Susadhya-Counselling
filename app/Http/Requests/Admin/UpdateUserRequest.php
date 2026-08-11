<?php

namespace App\Http\Requests\Admin;

use App\Support\RoleAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules(): array
    {
        $subject = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($subject)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'role' => ['required', Rule::in(RoleAccess::namesAssignableBy($this->user()))],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [function ($validator): void {
            $subject = $this->route('user');

            if ($subject->is($this->user()) && ! $this->boolean('is_active')) {
                $validator->errors()->add('is_active', 'You cannot deactivate your own account.');
            }

            if ($subject->isSuperAdmin() && $this->input('role') !== 'super_admin') {
                $validator->errors()->add('role', 'The primary super-administrator role cannot be changed.');
            }
        }];
    }
}

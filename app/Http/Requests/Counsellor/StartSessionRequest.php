<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CounsellorProfile;
use Illuminate\Foundation\Http\FormRequest;

class StartSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('counsellor')
            && CounsellorProfile::query()
                ->where('user_id', $this->user()->id)
                ->exists();
    }

    public function rules(): array
    {
        return [];
    }
}

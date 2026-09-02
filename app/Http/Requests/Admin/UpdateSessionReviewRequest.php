<?php

namespace App\Http\Requests\Admin;

use App\Models\CounsellingSession;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSessionReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole([
            'admin',
            'super_admin',
        ]) ?? false;
    }

    public function rules(): array
    {
        return [
            'clinical_risk_level' => [
                'required',
                Rule::in(CounsellingSession::riskLevels()),
            ],
            'follow_up_recommended' => ['nullable', 'boolean'],
            'follow_up_notes' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:8000'],
        ];
    }
}

<?php

namespace App\Http\Requests\Counsellor;

use App\Models\CounsellorProfile;
use App\Models\SessionNote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSessionNoteRequest extends FormRequest
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
        return [
            'note_type' => [
                'required',
                Rule::in(SessionNote::noteTypes()),
            ],
            'visibility' => [
                'required',
                Rule::in(SessionNote::visibilities()),
            ],
            'content' => ['required', 'string', 'max:8000'],
        ];
    }

    public function messages(): array
    {
        return [
            'note_type.required' => 'Please select note type.',
            'visibility.required' => 'Please select note visibility.',
            'content.required' => 'Please enter a session note.',
        ];
    }
}

<?php

namespace App\Http\Requests\Counsellor;

use App\Models\SecureDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCaseDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:160',
            ],

            'category' => [
                'required',

                Rule::in([
                    SecureDocument::CATEGORY_CONSENT,
                    SecureDocument::CATEGORY_CLINICAL,
                    SecureDocument::CATEGORY_REPORT,
                    SecureDocument::CATEGORY_OTHER,
                ]),
            ],

            'access_scope' => [
                'required',

                Rule::in([
                    SecureDocument::SCOPE_CARE_TEAM,
                    SecureDocument::SCOPE_CLINICAL,
                    SecureDocument::SCOPE_SHARED,
                ]),
            ],

            'file' => [
                'required',
                'file',

                'max:'.config(
                    'documents.max_size_kb',
                    10240
                ),

                'mimetypes:'.implode(
                    ',',
                    config(
                        'documents.allowed_mime_types',
                        []
                    )
                ),
            ],
        ];
    }
}

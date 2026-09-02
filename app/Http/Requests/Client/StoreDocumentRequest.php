<?php

namespace App\Http\Requests\Client;

use App\Models\SecureDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
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
                    SecureDocument::CATEGORY_CLIENT_UPLOAD,
                    SecureDocument::CATEGORY_CONSENT,
                    SecureDocument::CATEGORY_OTHER,
                ]),
            ],

            'access_scope' => [
                'required',

                Rule::in([
                    SecureDocument::SCOPE_CLIENT,
                    SecureDocument::SCOPE_CARE_TEAM,
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

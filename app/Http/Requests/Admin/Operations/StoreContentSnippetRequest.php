<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\ContentSnippet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContentSnippetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'admin.content.manage'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'string',
                'max:150',
                'regex:/^[a-z0-9._-]+$/',
                'unique:content_snippets,key',
            ],
            'title' => [
                'required',
                'string',
                'max:180',
            ],
            'body' => [
                'required',
                'string',
                'max:10000',
            ],
            'placement' => [
                'required',
                'string',
                'max:80',
            ],
            'status' => [
                'required',
                Rule::in(ContentSnippet::statuses()),
            ],
        ];
    }
}

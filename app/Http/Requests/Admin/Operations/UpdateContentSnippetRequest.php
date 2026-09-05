<?php

namespace App\Http\Requests\Admin\Operations;

use App\Models\ContentSnippet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContentSnippetRequest extends FormRequest
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

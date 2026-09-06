<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveCmsTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.testimonials.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'display_name' => [
                'required',
                'string',
                'max:120',
            ],

            'role_label' => [
                'nullable',
                'string',
                'max:160',
            ],

            'quote' => [
                'required',
                'string',
                'max:10000',
            ],

            'rating' => [
                'nullable',
                'integer',
                'min:1',
                'max:5',
            ],

            'image_path' => [
                'nullable',
                'string',
                'max:500',
            ],

            'is_featured' => [
                'required',
                'boolean',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'display_order' => [
                'required',
                'integer',
                'min:0',
                'max:65535',
            ],

            'consent_confirmed' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (
                Validator $validator
            ): void {
                if (
                    $this->boolean(
                        'is_active'
                    )
                    &&
                    ! $this->boolean(
                        'consent_confirmed'
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'consent_confirmed',
                            'Publishing a testimonial requires explicit confirmation that publication consent has been obtained.'
                        );
                }
            },
        ];
    }
}

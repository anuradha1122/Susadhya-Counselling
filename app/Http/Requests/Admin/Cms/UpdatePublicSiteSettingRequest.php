<?php

namespace App\Http\Requests\Admin\Cms;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicSiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'cms.settings.manage'
        ) === true;
    }

    public function rules(): array
    {
        return [
            'site_name' => [
                'required',
                'string',
                'max:160',
            ],

            'tagline' => [
                'nullable',
                'string',
                'max:255',
            ],

            'logo_path' => [
                'nullable',
                'string',
                'max:500',
            ],

            'favicon_path' => [
                'nullable',
                'string',
                'max:500',
            ],

            'contact_email' => [
                'nullable',
                'email',
                'max:190',
            ],

            'contact_phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'whatsapp_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'address' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'office_hours' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'social_links' => [
                'nullable',
                'array',
            ],

            'social_links.facebook' => [
                'nullable',
                'url',
                'max:500',
            ],

            'social_links.instagram' => [
                'nullable',
                'url',
                'max:500',
            ],

            'social_links.linkedin' => [
                'nullable',
                'url',
                'max:500',
            ],

            'social_links.youtube' => [
                'nullable',
                'url',
                'max:500',
            ],

            'default_meta_title' => [
                'nullable',
                'string',
                'max:180',
            ],

            'default_meta_description' => [
                'nullable',
                'string',
                'max:320',
            ],

            'default_og_image_path' => [
                'nullable',
                'string',
                'max:500',
            ],

            'footer_text' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'emergency_notice' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'booking_cta_label' => [
                'nullable',
                'string',
                'max:100',
            ],

            'booking_cta_url' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }
}

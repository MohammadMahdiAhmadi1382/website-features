<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebsiteFeatureRequest extends FormRequest
{ 
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique(config('website-features.table', 'website_features'), 'slug'),
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:100',
            ],
            'color' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^(#[0-9A-Fa-f]{3}|#[0-9A-Fa-f]{6}|#[0-9A-Fa-f]{8})$/',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
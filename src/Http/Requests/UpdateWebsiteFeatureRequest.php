<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebsiteFeatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $feature = $this->route('feature');

        $featureId = is_object($feature)
            ? $feature->getKey()
            : $feature;

        return [
            'title' => [
                'sometimes',
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                // Rule::unique(
                //     config(
                //         'website-features.table',
                //         'website_features',
                //     ),
                //     'slug',
                // )->ignore($featureId),
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
            ],

            'icon' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'color' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
                'regex:/^(#[0-9A-Fa-f]{3}|#[0-9A-Fa-f]{6}|#[0-9A-Fa-f]{8})$/',
            ],

            'sort_order' => [
                'sometimes',
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
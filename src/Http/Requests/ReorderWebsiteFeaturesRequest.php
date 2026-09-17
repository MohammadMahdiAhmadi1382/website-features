<?php

declare(strict_types=1);

namespace Codav\WebsiteFeatures\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderWebsiteFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'features' => [
                'required',
                'array',
                'min:1',
            ],
            'features.*' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],
        ];
    }
}
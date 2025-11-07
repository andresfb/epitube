<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ContentUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $content = $this->route('slug');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('contents', 'slug')->ignore($content, 'slug')],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'service_url' => ['nullable', 'url', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.required' => 'The slug field is required.',
            'slug.unique' => 'This slug is already in use.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category does not exist.',
            'service_url.url' => 'The service URL must be a valid URL.',
        ];
    }
}

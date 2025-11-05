<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ContentListRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer', 'exists:categories'],
            'title' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
            'viewed' => ['nullable', 'boolean'],
            'like_status' => ['nullable', 'integer'],
            'added_at' => ['nullable', 'date'],
            'created_at' => ['nullable', 'date'],
            'tag' => ['nullable', 'string'],
            'search' => ['nullable', 'string'],
            'sort' => ['nullable', 'string'],
            'page' => ['nullable', 'integer'],
        ];
    }
}

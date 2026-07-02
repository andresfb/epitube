<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => 'string|required|min:2',
            'viewed' => 'nullable|boolean',
            'liked' => 'nullable|integer|min:-1|max:1',
        ];
    }
}

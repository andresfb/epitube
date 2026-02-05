<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RandomVideoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'integer'],
            'tag' => ['nullable', 'string'],
            'count' => ['nullable', 'integer', 'min:5'],
        ];
    }
}

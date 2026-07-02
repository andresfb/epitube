<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WordSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => 'string|required|min:2',
            'count' => 'nullable|integer|min:1',
        ];
    }
}

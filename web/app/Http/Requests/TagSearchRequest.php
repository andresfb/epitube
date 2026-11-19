<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TagSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'term' => ['string', 'required', 'min:2'],
        ];
    }
}

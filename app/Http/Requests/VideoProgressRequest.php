<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VideoProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_time' => 'required|numeric',
            'duration' => 'required|numeric',
            'completed' => 'required|boolean',
        ];
    }
}

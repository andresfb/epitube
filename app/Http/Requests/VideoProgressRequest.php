<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoProgressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_time' => 'required|numeric:',
            'duration' => 'required|numeric',
            'completed' => 'required|boolean',
        ];
    }
}

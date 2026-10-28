<?php

namespace App\Http\Requests\Doctor;

use Illuminate\Foundation\Http\FormRequest;

class Update extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pid' => 'required|string|max:255',
            'name_real' => 'required|string|max:255',
            'poli' => 'required|string|max:255',
            'spesialis' => 'required|string|max:255',
            'url_image' => 'nullable',
        ];
    }
}
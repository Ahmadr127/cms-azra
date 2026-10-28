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
            'name_real' => 'nullable|string|max:255',
            'poli' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:255',
            'spesialis' => 'nullable|string|max:255',
            'url_image' => 'nullable|image|max:5120',
            'riwayat' => 'nullable|array',
            'riwayat.*.description' => 'nullable|string|max:255',
            'kursus' => 'nullable|array',
            'kursus.*.description' => 'nullable|string|max:255',
        ];
    }
}
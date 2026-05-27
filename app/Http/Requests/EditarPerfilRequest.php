<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EditarPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'min:3', 'max:100'],
            'email'   => ['required', 'email', Rule::unique('users', 'email')->ignore(auth()->id())],
            'bio'     => ['nullable', 'string', 'max:500'],
            'ciudad'  => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio',
            'name.min'       => 'El nombre debe tener al menos 3 caracteres',
            'email.required' => 'El email es obligatorio',
            'email.email'    => 'Introduce un email válido',
            'email.unique'   => 'Este email ya está en uso',
            'bio.max'        => 'La biografía no puede superar los 500 caracteres',
        ];
    }
}

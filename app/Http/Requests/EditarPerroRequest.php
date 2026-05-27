<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditarPerroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nombre'        => ['required', 'string', 'min:2', 'max:50'],
            'raza'          => ['nullable', 'string', 'max:80'],
            'edad_anios'    => ['nullable', 'integer', 'min:0', 'max:25'],
            'peso_kg'       => ['nullable', 'numeric', 'min:0', 'max:120'],
            'sexo'          => ['required', 'in:macho,hembra'],
            'esterilizado'  => ['boolean'],
            'vacunado'      => ['boolean'],
            'energia'       => ['required', 'integer', 'between:1,5'],
            'caracter'      => ['nullable', 'array'],
            'caracter.*'    => ['string', 'max:30'],
            'notas'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required'  => 'El nombre del perro es obligatorio',
            'nombre.min'       => 'El nombre debe tener al menos 2 caracteres',
            'sexo.required'    => 'Selecciona el sexo',
            'sexo.in'          => 'Sexo no válido',
            'energia.required' => 'Indica el nivel de energía',
            'energia.between'  => 'La energía debe estar entre 1 y 5',
            'peso_kg.numeric'  => 'El peso debe ser un número',
            'peso_kg.max'      => 'Peso máximo: 120 kg',
        ];
    }
}

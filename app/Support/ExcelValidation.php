<?php

namespace App\Support;

class ExcelValidation
{
    public static function messages(): array
    {
        return [
            'required' => 'El campo :attribute es requerido.',
            'string'   => 'El campo :attribute debe ser texto.',
            'integer'  => 'El campo :attribute debe ser un número entero.',
            'numeric'  => 'El campo :attribute debe ser numérico.',
            'min'      => 'El campo :attribute no cumple con el mínimo permitido.',
            'max'      => 'El campo :attribute excede el límite permitido.',
            'between'  => 'El campo :attribute está fuera del rango permitido.',
            'url'      => 'El campo :attribute debe ser una URL válida.',
        ];
    }
}

<?php

namespace App\Http\Requests\Categoria;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRevisor() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('categorias', 'nombre')->ignore($this->route('categoria')),
            ],
            'activa' => ['sometimes', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Solicitud;

use App\Enums\SolicitudEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['sometimes', Rule::enum(SolicitudEstado::class)],
            'categoria_id' => ['sometimes', 'integer', 'exists:categorias,id'],
            'sort_by' => ['sometimes', Rule::in(['created_at', 'estado', 'tipo'])],
            'sort_dir' => ['sometimes', Rule::in(['asc', 'desc'])],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}

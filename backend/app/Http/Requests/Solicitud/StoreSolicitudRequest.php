<?php

namespace App\Http\Requests\Solicitud;

use App\Enums\SolicitudTipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSolicitudRequest extends FormRequest
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
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'tipo' => ['required', Rule::enum(SolicitudTipo::class)],
            'descripcion' => ['required', 'string', 'max:2000'],
            'campos_adicionales' => ['sometimes', 'array'],
            'campos_adicionales.urgencia' => [
                Rule::requiredIf(fn () => $this->input('tipo') === SolicitudTipo::Incidente->value),
                'nullable',
                'string',
                Rule::in(['baja', 'media', 'alta']),
            ],
        ];
    }
}

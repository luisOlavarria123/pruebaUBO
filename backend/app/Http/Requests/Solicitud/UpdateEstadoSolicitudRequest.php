<?php

namespace App\Http\Requests\Solicitud;

use App\Enums\SolicitudEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEstadoSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('updateEstado', $this->route('solicitud')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::enum(SolicitudEstado::class)],
        ];
    }
}

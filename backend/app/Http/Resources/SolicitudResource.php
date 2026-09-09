<?php

namespace App\Http\Resources;

use App\Enums\SolicitudEstado;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Solicitud */
class SolicitudResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo->value,
            'descripcion' => $this->descripcion,
            'campos_adicionales' => $this->campos_adicionales,
            'estado' => $this->estado->value,
            'estados_siguientes' => array_values(array_map(
                fn (SolicitudEstado $e) => $e->value,
                array_filter(
                    SolicitudEstado::cases(),
                    fn (SolicitudEstado $e) => $this->estado->canTransitionTo($e),
                ),
            )),
            'solicitante' => new UserResource($this->whenLoaded('solicitante')),
            'categoria' => new CategoriaResource($this->whenLoaded('categoria')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Enums;

enum SolicitudTipo: string
{
    case Incidente = 'incidente';
    case Consulta = 'consulta';
    case Mejora = 'mejora';

    /**
     * Campos de `campos_adicionales` que son obligatorios para poder
     * aprobar una solicitud de este tipo (regla de negocio no trivial).
     *
     * @return array<int, string>
     */
    public function camposRequeridos(): array
    {
        return match ($this) {
            self::Incidente => ['urgencia'],
            self::Consulta, self::Mejora => [],
        };
    }
}

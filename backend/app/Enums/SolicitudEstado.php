<?php

namespace App\Enums;

enum SolicitudEstado: string
{
    case Pendiente = 'pendiente';
    case EnRevision = 'en_revision';
    case Aprobada = 'aprobada';
    case Rechazada = 'rechazada';

    /**
     * Unica fuente de verdad de la maquina de estados: que transiciones
     * son legales desde cada estado.
     */
    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Pendiente => $next === self::EnRevision,
            self::EnRevision => in_array($next, [self::Aprobada, self::Rechazada], true),
            self::Aprobada, self::Rechazada => false,
        };
    }

    public function esFinal(): bool
    {
        return $this === self::Aprobada || $this === self::Rechazada;
    }
}

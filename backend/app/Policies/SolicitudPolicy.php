<?php

namespace App\Policies;

use App\Enums\SolicitudEstado;
use App\Models\Solicitud;
use App\Models\User;

class SolicitudPolicy
{
    /**
     * Un solicitante solo puede ver sus propias solicitudes; el revisor ve todas.
     */
    public function view(User $user, Solicitud $solicitud): bool
    {
        return $user->isRevisor() || $solicitud->solicitante_id === $user->id;
    }

    /**
     * Solo el dueno puede editar el contenido, y solo mientras sigue pendiente
     * (una vez que entra a revision, editarla bajo los pies del revisor seria
     * un bug de negocio, no una feature).
     */
    public function update(User $user, Solicitud $solicitud): bool
    {
        return $solicitud->solicitante_id === $user->id
            && $solicitud->estado === SolicitudEstado::Pendiente;
    }

    /**
     * Solo el revisor puede cambiar el estado de una solicitud.
     */
    public function updateEstado(User $user, Solicitud $solicitud): bool
    {
        return $user->isRevisor();
    }

    /**
     * Solo el revisor puede eliminar una solicitud.
     */
    public function delete(User $user, Solicitud $solicitud): bool
    {
        return $user->isRevisor();
    }
}

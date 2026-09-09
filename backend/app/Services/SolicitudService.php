<?php

namespace App\Services;

use App\Enums\SolicitudEstado;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SolicitudService
{
    /**
     * Listado paginado, filtrado y ordenado. Un solicitante solo ve las propias;
     * el revisor ve todas.
     *
     * @param  array{estado?: string, categoria_id?: int, sort_by?: string, sort_dir?: string, per_page?: int}  $filters
     */
    public function list(User $user, array $filters): LengthAwarePaginator
    {
        $query = Solicitud::query()->with(['solicitante', 'categoria']);

        if ($user->role === UserRole::Solicitante) {
            $query->where('solicitante_id', $user->id);
        }

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['categoria_id'])) {
            $query->where('categoria_id', $filters['categoria_id']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    /**
     * @param  array{categoria_id: int, tipo: string, descripcion: string, campos_adicionales?: array<string, mixed>}  $data
     */
    public function create(User $solicitante, array $data): Solicitud
    {
        return Solicitud::create([
            'solicitante_id' => $solicitante->id,
            'categoria_id' => $data['categoria_id'],
            'tipo' => $data['tipo'],
            'descripcion' => $data['descripcion'],
            'campos_adicionales' => $data['campos_adicionales'] ?? [],
            'estado' => SolicitudEstado::Pendiente,
        ]);
    }

    /**
     * Edita el contenido de la solicitud. Restringido por la policy a su
     * dueno y solo mientras esta en estado "pendiente".
     *
     * @param  array{categoria_id: int, tipo: string, descripcion: string, campos_adicionales?: array<string, mixed>}  $data
     */
    public function update(Solicitud $solicitud, array $data): Solicitud
    {
        $solicitud->update([
            'categoria_id' => $data['categoria_id'],
            'tipo' => $data['tipo'],
            'descripcion' => $data['descripcion'],
            'campos_adicionales' => $data['campos_adicionales'] ?? [],
        ]);

        return $solicitud->fresh(['solicitante', 'categoria']);
    }

    /**
     * Aplica la transicion de estado validando la maquina de estados y,
     * al aprobar, la regla de negocio no trivial (categoria activa +
     * campos obligatorios segun el tipo).
     *
     * @throws ValidationException
     */
    public function transitionEstado(Solicitud $solicitud, SolicitudEstado $nuevoEstado): Solicitud
    {
        if (! $solicitud->estado->canTransitionTo($nuevoEstado)) {
            throw ValidationException::withMessages([
                'estado' => "No se puede pasar de \"{$solicitud->estado->value}\" a \"{$nuevoEstado->value}\".",
            ]);
        }

        if ($nuevoEstado === SolicitudEstado::Aprobada) {
            $this->assertAprobable($solicitud);
        }

        $solicitud->update(['estado' => $nuevoEstado]);

        return $solicitud->fresh(['solicitante', 'categoria']);
    }

    /**
     * @throws ValidationException
     */
    private function assertAprobable(Solicitud $solicitud): void
    {
        /** @var Categoria $categoria */
        $categoria = $solicitud->categoria ?? $solicitud->categoria()->firstOrFail();

        if (! $categoria->activa) {
            throw ValidationException::withMessages([
                'estado' => "No se puede aprobar: la categoria \"{$categoria->nombre}\" esta inactiva.",
            ]);
        }

        $faltantes = array_filter(
            $solicitud->tipo->camposRequeridos(),
            fn (string $campo) => empty($solicitud->campos_adicionales[$campo] ?? null),
        );

        if ($faltantes !== []) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede aprobar: faltan campos obligatorios para el tipo "'
                    .$solicitud->tipo->value.'": '.implode(', ', $faltantes).'.',
            ]);
        }
    }
}

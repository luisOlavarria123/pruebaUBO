<?php

namespace Database\Factories;

use App\Enums\SolicitudEstado;
use App\Enums\SolicitudTipo;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Solicitud>
 */
class SolicitudFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tipo = fake()->randomElement(SolicitudTipo::cases());

        return [
            'solicitante_id' => User::factory(),
            'categoria_id' => Categoria::factory(),
            'tipo' => $tipo,
            'descripcion' => fake()->sentence(12),
            'campos_adicionales' => $tipo === SolicitudTipo::Incidente
                ? ['urgencia' => fake()->randomElement(['baja', 'media', 'alta'])]
                : [],
            'estado' => SolicitudEstado::Pendiente,
        ];
    }

    public function enRevision(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => SolicitudEstado::EnRevision]);
    }

    public function aprobada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => SolicitudEstado::Aprobada]);
    }

    public function rechazada(): static
    {
        return $this->state(fn (array $attributes) => ['estado' => SolicitudEstado::Rechazada]);
    }
}

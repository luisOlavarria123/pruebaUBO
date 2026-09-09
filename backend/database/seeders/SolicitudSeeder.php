<?php

namespace Database\Seeders;

use App\Enums\SolicitudEstado;
use App\Enums\SolicitudTipo;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Database\Seeder;

class SolicitudSeeder extends Seeder
{
    public function run(): void
    {
        $solicitante = User::where('email', 'solicitante@ubo.test')->firstOrFail();
        $infraestructura = Categoria::where('nombre', 'Infraestructura')->firstOrFail();
        $software = Categoria::where('nombre', 'Software')->firstOrFail();
        $accesos = Categoria::where('nombre', 'Accesos')->firstOrFail();
        $legado = Categoria::where('nombre', 'Legado (descontinuada)')->firstOrFail();

        $solicitudes = [
            [
                'descripcion' => 'El servidor de correo no responde desde esta manana.',
                'categoria_id' => $infraestructura->id,
                'tipo' => SolicitudTipo::Incidente,
                'campos_adicionales' => ['urgencia' => 'alta'],
                'estado' => SolicitudEstado::Pendiente,
            ],
            [
                'descripcion' => 'Solicito acceso de lectura al repositorio de reportes.',
                'categoria_id' => $accesos->id,
                'tipo' => SolicitudTipo::Consulta,
                'campos_adicionales' => [],
                'estado' => SolicitudEstado::EnRevision,
            ],
            [
                'descripcion' => 'Actualizar la version de la libreria de reportes a la ultima estable.',
                'categoria_id' => $software->id,
                'tipo' => SolicitudTipo::Mejora,
                'campos_adicionales' => [],
                'estado' => SolicitudEstado::Aprobada,
            ],
            [
                'descripcion' => 'La impresora del tercer piso no imprime a color.',
                'categoria_id' => $infraestructura->id,
                'tipo' => SolicitudTipo::Incidente,
                'campos_adicionales' => ['urgencia' => 'baja'],
                'estado' => SolicitudEstado::Rechazada,
            ],
            [
                'descripcion' => 'Necesito soporte sobre un sistema que ya no se mantiene.',
                'categoria_id' => $legado->id,
                'tipo' => SolicitudTipo::Consulta,
                'campos_adicionales' => [],
                'estado' => SolicitudEstado::EnRevision,
            ],
        ];

        foreach ($solicitudes as $data) {
            Solicitud::updateOrCreate(
                ['descripcion' => $data['descripcion']],
                [
                    'solicitante_id' => $solicitante->id,
                    'categoria_id' => $data['categoria_id'],
                    'tipo' => $data['tipo'],
                    'campos_adicionales' => $data['campos_adicionales'],
                    'estado' => $data['estado'],
                ],
            );
        }
    }
}

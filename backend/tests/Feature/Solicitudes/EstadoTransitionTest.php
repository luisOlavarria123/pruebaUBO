<?php

namespace Tests\Feature\Solicitudes;

use App\Enums\SolicitudEstado;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstadoTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_revisor_puede_pasar_de_pendiente_a_en_revision(): void
    {
        $revisor = User::factory()->revisor()->create();
        $solicitud = Solicitud::factory()->create(['estado' => SolicitudEstado::Pendiente]);

        $this->actingAs($revisor, 'api')
            ->patchJson("/api/solicitudes/{$solicitud->id}/estado", ['estado' => 'en_revision'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'en_revision');
    }

    public function test_no_se_puede_pasar_de_pendiente_a_aprobada_directamente(): void
    {
        $revisor = User::factory()->revisor()->create();
        $solicitud = Solicitud::factory()->create(['estado' => SolicitudEstado::Pendiente]);

        $this->actingAs($revisor, 'api')
            ->patchJson("/api/solicitudes/{$solicitud->id}/estado", ['estado' => 'aprobada'])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);

        $this->assertSame(SolicitudEstado::Pendiente, $solicitud->fresh()->estado);
    }

    public function test_un_solicitante_no_puede_cambiar_el_estado_de_una_solicitud(): void
    {
        $solicitante = User::factory()->create(['role' => UserRole::Solicitante]);
        $solicitud = Solicitud::factory()->create(['estado' => SolicitudEstado::EnRevision]);

        $this->actingAs($solicitante, 'api')
            ->patchJson("/api/solicitudes/{$solicitud->id}/estado", ['estado' => 'aprobada'])
            ->assertStatus(403);
    }

    public function test_no_se_puede_aprobar_una_solicitud_cuya_categoria_esta_inactiva(): void
    {
        $revisor = User::factory()->revisor()->create();
        $categoria = Categoria::factory()->inactiva()->create();
        $solicitud = Solicitud::factory()->create([
            'categoria_id' => $categoria->id,
            'estado' => SolicitudEstado::EnRevision,
            'tipo' => 'consulta',
            'campos_adicionales' => [],
        ]);

        $this->actingAs($revisor, 'api')
            ->patchJson("/api/solicitudes/{$solicitud->id}/estado", ['estado' => 'aprobada'])
            ->assertStatus(422);
    }

    public function test_no_se_puede_aprobar_un_incidente_sin_el_campo_urgencia(): void
    {
        $revisor = User::factory()->revisor()->create();
        $solicitud = Solicitud::factory()->create([
            'estado' => SolicitudEstado::EnRevision,
            'tipo' => 'incidente',
            'campos_adicionales' => [],
        ]);

        $this->actingAs($revisor, 'api')
            ->patchJson("/api/solicitudes/{$solicitud->id}/estado", ['estado' => 'aprobada'])
            ->assertStatus(422);
    }

    public function test_se_puede_aprobar_un_incidente_con_urgencia_y_categoria_activa(): void
    {
        $revisor = User::factory()->revisor()->create();
        $solicitud = Solicitud::factory()->create([
            'estado' => SolicitudEstado::EnRevision,
            'tipo' => 'incidente',
            'campos_adicionales' => ['urgencia' => 'alta'],
        ]);

        $this->actingAs($revisor, 'api')
            ->patchJson("/api/solicitudes/{$solicitud->id}/estado", ['estado' => 'aprobada'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'aprobada');
    }
}

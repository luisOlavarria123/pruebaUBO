<?php

namespace Tests\Feature\Solicitudes;

use App\Enums\SolicitudEstado;
use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_dueno_puede_editar_su_solicitud_mientras_esta_pendiente(): void
    {
        $solicitante = User::factory()->create(['role' => UserRole::Solicitante]);
        $categoria = Categoria::factory()->create();
        $solicitud = Solicitud::factory()->create([
            'solicitante_id' => $solicitante->id,
            'estado' => SolicitudEstado::Pendiente,
        ]);

        $this->actingAs($solicitante, 'api')
            ->putJson("/api/solicitudes/{$solicitud->id}", [
                'categoria_id' => $categoria->id,
                'tipo' => 'consulta',
                'descripcion' => 'Descripcion editada',
            ])
            ->assertOk()
            ->assertJsonPath('data.descripcion', 'Descripcion editada');
    }

    public function test_no_se_puede_editar_una_solicitud_que_ya_no_esta_pendiente(): void
    {
        $solicitante = User::factory()->create(['role' => UserRole::Solicitante]);
        $solicitud = Solicitud::factory()->create([
            'solicitante_id' => $solicitante->id,
            'estado' => SolicitudEstado::EnRevision,
        ]);

        $this->actingAs($solicitante, 'api')
            ->putJson("/api/solicitudes/{$solicitud->id}", [
                'categoria_id' => $solicitud->categoria_id,
                'tipo' => 'consulta',
                'descripcion' => 'Intento de edicion tardia',
            ])
            ->assertStatus(403);
    }

    public function test_un_usuario_no_puede_editar_la_solicitud_de_otro(): void
    {
        $dueno = User::factory()->create(['role' => UserRole::Solicitante]);
        $otro = User::factory()->create(['role' => UserRole::Solicitante]);
        $solicitud = Solicitud::factory()->create([
            'solicitante_id' => $dueno->id,
            'estado' => SolicitudEstado::Pendiente,
        ]);

        $this->actingAs($otro, 'api')
            ->putJson("/api/solicitudes/{$solicitud->id}", [
                'categoria_id' => $solicitud->categoria_id,
                'tipo' => 'consulta',
                'descripcion' => 'No deberia poder',
            ])
            ->assertStatus(403);
    }

    public function test_solo_el_revisor_puede_eliminar_una_solicitud(): void
    {
        $revisor = User::factory()->revisor()->create();
        $solicitud = Solicitud::factory()->create();

        $this->actingAs($revisor, 'api')
            ->deleteJson("/api/solicitudes/{$solicitud->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('solicitudes', ['id' => $solicitud->id]);
    }

    public function test_el_dueno_no_puede_eliminar_su_propia_solicitud(): void
    {
        $solicitante = User::factory()->create(['role' => UserRole::Solicitante]);
        $solicitud = Solicitud::factory()->create(['solicitante_id' => $solicitante->id]);

        $this->actingAs($solicitante, 'api')
            ->deleteJson("/api/solicitudes/{$solicitud->id}")
            ->assertStatus(403);

        $this->assertDatabaseHas('solicitudes', ['id' => $solicitud->id]);
    }
}

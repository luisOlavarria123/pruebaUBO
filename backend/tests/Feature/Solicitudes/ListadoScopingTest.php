<?php

namespace Tests\Feature\Solicitudes;

use App\Enums\UserRole;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListadoScopingTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_solicitante_solo_ve_sus_propias_solicitudes(): void
    {
        $propio = User::factory()->create(['role' => UserRole::Solicitante]);
        $otro = User::factory()->create(['role' => UserRole::Solicitante]);

        Solicitud::factory()->count(2)->create(['solicitante_id' => $propio->id]);
        Solicitud::factory()->count(3)->create(['solicitante_id' => $otro->id]);

        $response = $this->actingAs($propio, 'api')->getJson('/api/solicitudes');

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    public function test_el_revisor_ve_todas_las_solicitudes(): void
    {
        $revisor = User::factory()->revisor()->create();
        $solicitante = User::factory()->create(['role' => UserRole::Solicitante]);

        Solicitud::factory()->count(4)->create(['solicitante_id' => $solicitante->id]);

        $response = $this->actingAs($revisor, 'api')->getJson('/api/solicitudes');

        $response->assertOk();
        $this->assertCount(4, $response->json('data'));
    }

    public function test_un_solicitante_no_puede_ver_la_solicitud_de_otro_usuario(): void
    {
        $propio = User::factory()->create(['role' => UserRole::Solicitante]);
        $otro = User::factory()->create(['role' => UserRole::Solicitante]);
        $solicitud = Solicitud::factory()->create(['solicitante_id' => $otro->id]);

        $this->actingAs($propio, 'api')
            ->getJson("/api/solicitudes/{$solicitud->id}")
            ->assertStatus(403);
    }

    public function test_sin_token_las_rutas_protegidas_devuelven_401(): void
    {
        $this->getJson('/api/solicitudes')->assertStatus(401);
    }
}

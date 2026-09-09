<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Categoria;
use App\Models\Solicitud;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriaCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_revisor_puede_eliminar_una_categoria_sin_solicitudes(): void
    {
        $revisor = User::factory()->revisor()->create();
        $categoria = Categoria::factory()->create();

        $this->actingAs($revisor, 'api')
            ->deleteJson("/api/categorias/{$categoria->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('categorias', ['id' => $categoria->id]);
    }

    public function test_no_se_puede_eliminar_una_categoria_con_solicitudes_asociadas(): void
    {
        $revisor = User::factory()->revisor()->create();
        $categoria = Categoria::factory()->create();
        Solicitud::factory()->create(['categoria_id' => $categoria->id]);

        $this->actingAs($revisor, 'api')
            ->deleteJson("/api/categorias/{$categoria->id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('categorias', ['id' => $categoria->id]);
    }

    public function test_un_solicitante_no_puede_eliminar_categorias(): void
    {
        $solicitante = User::factory()->create(['role' => UserRole::Solicitante]);
        $categoria = Categoria::factory()->create();

        $this->actingAs($solicitante, 'api')
            ->deleteJson("/api/categorias/{$categoria->id}")
            ->assertStatus(403);
    }
}

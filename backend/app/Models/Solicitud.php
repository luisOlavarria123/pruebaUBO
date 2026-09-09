<?php

namespace App\Models;

use App\Enums\SolicitudEstado;
use App\Enums\SolicitudTipo;
use Database\Factories\SolicitudFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $solicitante_id
 * @property int $categoria_id
 * @property SolicitudTipo $tipo
 * @property string $descripcion
 * @property array<string, mixed> $campos_adicionales
 * @property SolicitudEstado $estado
 */
#[Fillable(['solicitante_id', 'categoria_id', 'tipo', 'descripcion', 'campos_adicionales', 'estado'])]
class Solicitud extends Model
{
    /** @use HasFactory<SolicitudFactory> */
    use HasFactory;

    protected $table = 'solicitudes';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo' => SolicitudTipo::class,
            'estado' => SolicitudEstado::class,
            'campos_adicionales' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    /**
     * @return BelongsTo<Categoria, $this>
     */
    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }
}

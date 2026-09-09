<?php

use App\Enums\SolicitudEstado;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitante_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
            $table->string('tipo');
            $table->text('descripcion');
            $table->json('campos_adicionales')->nullable();
            $table->string('estado')->default(SolicitudEstado::Pendiente->value);
            $table->timestamps();

            $table->index('estado');
            $table->index('categoria_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};

<?php

namespace Tests\Unit;

use App\Enums\SolicitudEstado;
use PHPUnit\Framework\TestCase;

class SolicitudEstadoTest extends TestCase
{
    public function test_pendiente_solo_puede_pasar_a_en_revision(): void
    {
        $this->assertTrue(SolicitudEstado::Pendiente->canTransitionTo(SolicitudEstado::EnRevision));
        $this->assertFalse(SolicitudEstado::Pendiente->canTransitionTo(SolicitudEstado::Aprobada));
        $this->assertFalse(SolicitudEstado::Pendiente->canTransitionTo(SolicitudEstado::Rechazada));
        $this->assertFalse(SolicitudEstado::Pendiente->canTransitionTo(SolicitudEstado::Pendiente));
    }

    public function test_en_revision_puede_pasar_a_aprobada_o_rechazada(): void
    {
        $this->assertTrue(SolicitudEstado::EnRevision->canTransitionTo(SolicitudEstado::Aprobada));
        $this->assertTrue(SolicitudEstado::EnRevision->canTransitionTo(SolicitudEstado::Rechazada));
        $this->assertFalse(SolicitudEstado::EnRevision->canTransitionTo(SolicitudEstado::Pendiente));
    }

    public function test_los_estados_finales_no_transicionan_a_ningun_lado(): void
    {
        foreach (SolicitudEstado::cases() as $destino) {
            $this->assertFalse(SolicitudEstado::Aprobada->canTransitionTo($destino));
            $this->assertFalse(SolicitudEstado::Rechazada->canTransitionTo($destino));
        }
    }

    public function test_esfinal(): void
    {
        $this->assertTrue(SolicitudEstado::Aprobada->esFinal());
        $this->assertTrue(SolicitudEstado::Rechazada->esFinal());
        $this->assertFalse(SolicitudEstado::Pendiente->esFinal());
        $this->assertFalse(SolicitudEstado::EnRevision->esFinal());
    }
}

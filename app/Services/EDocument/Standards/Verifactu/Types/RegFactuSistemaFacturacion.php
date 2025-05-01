<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class RegFactuSistemaFacturacion
{
    /** @var PersonaFisicaJuridicaES */
    protected $ObligadoEmision;

    /** @var RegistroAlta */
    protected $RegistroAlta;

    public function getObligadoEmision(): PersonaFisicaJuridicaES
    {
        return $this->ObligadoEmision;
    }

    public function setObligadoEmision(PersonaFisicaJuridicaES $obligadoEmision): self
    {
        $this->ObligadoEmision = $obligadoEmision;
        return $this;
    }

    public function getRegistroAlta(): RegistroAlta
    {
        return $this->RegistroAlta;
    }

    public function setRegistroAlta(RegistroAlta $registroAlta): self
    {
        $this->RegistroAlta = $registroAlta;
        return $this;
    }
} 
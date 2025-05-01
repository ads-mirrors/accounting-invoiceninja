<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Cabecera
{
    /** @var ObligadoEmision */
    protected $obligadoEmision;

    /** @var PersonaFisicaJuridicaES|null */
    protected $representante;

    /** @var array{fechaFinVeriFactu?: string, incidencia?: IncidenciaType}|null */
    protected $remisionVoluntaria;

    /** @var array{refRequerimiento: string, finRequerimiento?: string}|null */
    protected $remisionRequerimiento;

    public function getObligadoEmision(): ObligadoEmision
    {
        return $this->obligadoEmision;
    }

    public function setObligadoEmision(ObligadoEmision $obligadoEmision): self
    {
        $this->obligadoEmision = $obligadoEmision;
        return $this;
    }

    public function getRepresentante(): ?PersonaFisicaJuridicaES
    {
        return $this->representante;
    }

    public function setRepresentante(?PersonaFisicaJuridicaES $representante): self
    {
        $this->representante = $representante;
        return $this;
    }

    /**
     * @return array{fechaFinVeriFactu?: string, incidencia?: IncidenciaType}|null
     */
    public function getRemisionVoluntaria(): ?array
    {
        return $this->remisionVoluntaria;
    }

    /**
     * @param array{fechaFinVeriFactu?: string, incidencia?: IncidenciaType}|null $remisionVoluntaria
     */
    public function setRemisionVoluntaria(?array $remisionVoluntaria): self
    {
        $this->remisionVoluntaria = $remisionVoluntaria;
        return $this;
    }

    /**
     * @return array{refRequerimiento: string, finRequerimiento?: string}|null
     */
    public function getRemisionRequerimiento(): ?array
    {
        return $this->remisionRequerimiento;
    }

    /**
     * @param array{refRequerimiento: string, finRequerimiento?: string}|null $remisionRequerimiento
     */
    public function setRemisionRequerimiento(?array $remisionRequerimiento): self
    {
        $this->remisionRequerimiento = $remisionRequerimiento;
        return $this;
    }
} 
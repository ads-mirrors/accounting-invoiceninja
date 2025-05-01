<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Cabecera
{
    /** @var PersonaFisicaJuridicaES */
    protected $ObligadoEmision;

    /** @var PersonaFisicaJuridicaES|null */
    protected $Representante;

    /** @var array{FechaFinVeriFactu?: string}|null */
    protected $RemisionVoluntaria;

    /** @var array{RefRequerimiento: string, FinRequerimiento?: string}|null */
    protected $RemisionRequerimiento;

    public function getObligadoEmision(): PersonaFisicaJuridicaES
    {
        return $this->ObligadoEmision;
    }

    public function setObligadoEmision(PersonaFisicaJuridicaES $obligadoEmision): self
    {
        $this->ObligadoEmision = $obligadoEmision;
        return $this;
    }

    public function getRepresentante(): ?PersonaFisicaJuridicaES
    {
        return $this->Representante;
    }

    public function setRepresentante(?PersonaFisicaJuridicaES $representante): self
    {
        $this->Representante = $representante;
        return $this;
    }

    /**
     * @return array{FechaFinVeriFactu?: string}|null
     */
    public function getRemisionVoluntaria(): ?array
    {
        return $this->RemisionVoluntaria;
    }

    /**
     * @param array{FechaFinVeriFactu?: string}|null $remisionVoluntaria
     */
    public function setRemisionVoluntaria(?array $remisionVoluntaria): self
    {
        if ($remisionVoluntaria !== null) {
            if (isset($remisionVoluntaria['FechaFinVeriFactu'])) {
                // Validate date format DD-MM-YYYY
                if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $remisionVoluntaria['FechaFinVeriFactu'])) {
                    throw new \InvalidArgumentException('FechaFinVeriFactu must be in DD-MM-YYYY format');
                }
                
                // Validate date components
                list($day, $month, $year) = explode('-', $remisionVoluntaria['FechaFinVeriFactu']);
                if (!checkdate((int)$month, (int)$day, (int)$year)) {
                    throw new \InvalidArgumentException('Invalid date in FechaFinVeriFactu');
                }
            }
        }
        $this->RemisionVoluntaria = $remisionVoluntaria;
        return $this;
    }

    /**
     * @return array{RefRequerimiento: string, FinRequerimiento?: string}|null
     */
    public function getRemisionRequerimiento(): ?array
    {
        return $this->RemisionRequerimiento;
    }

    /**
     * @param array{RefRequerimiento: string, FinRequerimiento?: string}|null $remisionRequerimiento
     */
    public function setRemisionRequerimiento(?array $remisionRequerimiento): self
    {
        if ($remisionRequerimiento !== null) {
            if (!isset($remisionRequerimiento['RefRequerimiento'])) {
                throw new \InvalidArgumentException('RefRequerimiento is required in RemisionRequerimiento');
            }

            if (isset($remisionRequerimiento['FinRequerimiento'])) {
                // Validate date format DD-MM-YYYY
                if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $remisionRequerimiento['FinRequerimiento'])) {
                    throw new \InvalidArgumentException('FinRequerimiento must be in DD-MM-YYYY format');
                }
                
                // Validate date components
                list($day, $month, $year) = explode('-', $remisionRequerimiento['FinRequerimiento']);
                if (!checkdate((int)$month, (int)$day, (int)$year)) {
                    throw new \InvalidArgumentException('Invalid date in FinRequerimiento');
                }
            }
        }
        $this->RemisionRequerimiento = $remisionRequerimiento;
        return $this;
    }
} 
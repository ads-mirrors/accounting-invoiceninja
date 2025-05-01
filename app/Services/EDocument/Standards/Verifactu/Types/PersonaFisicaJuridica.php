<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class PersonaFisicaJuridica
{
    /** @var string|null Max length 120 characters */
    protected $nombreRazon;

    /** @var string|null NIF format */
    protected $nif;

    /** @var IDOtro|null */
    protected $idOtro;

    public function getNombreRazon(): ?string
    {
        return $this->nombreRazon;
    }

    public function setNombreRazon(?string $nombreRazon): self
    {
        if ($nombreRazon !== null && strlen($nombreRazon) > 120) {
            throw new \InvalidArgumentException('NombreRazon must not exceed 120 characters');
        }
        $this->nombreRazon = $nombreRazon;
        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): self
    {
        // TODO: Add NIF validation
        if ($nif !== null && $this->idOtro !== null) {
            throw new \InvalidArgumentException('Cannot set both NIF and IDOtro');
        }
        $this->nif = $nif;
        return $this;
    }

    public function getIdOtro(): ?IDOtro
    {
        return $this->idOtro;
    }

    public function setIdOtro(?IDOtro $idOtro): self
    {
        if ($idOtro !== null && $this->nif !== null) {
            throw new \InvalidArgumentException('Cannot set both NIF and IDOtro');
        }
        $this->idOtro = $idOtro;
        return $this;
    }
} 
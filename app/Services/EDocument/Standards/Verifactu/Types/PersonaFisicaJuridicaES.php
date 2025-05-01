<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class PersonaFisicaJuridicaES
{
    /** @var string NIF format */
    protected $nif;

    /** @var string|null Max length 120 characters */
    protected $nombreRazon;

    public function getNif(): string
    {
        return $this->nif;
    }

    public function setNif(string $nif): self
    {
        // TODO: Add NIF validation
        $this->nif = $nif;
        return $this;
    }

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
} 
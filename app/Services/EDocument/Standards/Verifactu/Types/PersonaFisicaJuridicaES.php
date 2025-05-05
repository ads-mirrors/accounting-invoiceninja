<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

// User type is a person submitting on behalf of the company.
class PersonaFisicaJuridicaES
{
    /** @var string NIF format */
    #[SerializedName('sum1:NIF')]
    protected $NIF;

    /** @var string|null Max length 120 characters */
    #[SerializedName('sum1:NombreRazon')]
    protected $NombreRazon;

    public function getNIF(): string
    {
        return $this->NIF;
    }

    public function setNIF(string $nif): self
    {
        // Validate NIF format (letter or number followed by 8 numbers)
        if (!preg_match('/^[A-Z0-9][0-9]{8}$/', $nif)) {
            throw new \InvalidArgumentException('NIF must be a valid format (letter/number followed by 8 numbers)');
        }
        $this->NIF = $nif;
        return $this;
    }

    public function getNombreRazon(): ?string
    {
        return $this->NombreRazon;
    }

    public function setNombreRazon(?string $nombreRazon): self
    {
        if ($nombreRazon !== null && strlen($nombreRazon) > 120) {
            throw new \InvalidArgumentException('NombreRazon must not exceed 120 characters');
        }
        $this->NombreRazon = $nombreRazon;
        return $this;
    }
} 
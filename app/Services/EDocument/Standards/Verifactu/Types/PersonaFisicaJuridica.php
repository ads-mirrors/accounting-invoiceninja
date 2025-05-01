<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class PersonaFisicaJuridica
{
    /** @var string|null Max length 120 characters */
    #[SerializedName('sum1:NombreRazon')]
    protected $NombreRazon;

    /** @var string|null NIF format */
    #[SerializedName('sum1:NIF')]
    protected $NIF;

    /** @var IDOtro|null */
    #[SerializedName('sum1:IDOtro')]
    protected $IDOtro;

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

    public function getNIF(): ?string
    {
        return $this->NIF;
    }

    public function setNIF(?string $nif): self
    {
        // TODO: Add NIF validation
        if ($nif !== null && $this->IDOtro !== null) {
            throw new \InvalidArgumentException('Cannot set both NIF and IDOtro');
        }
        $this->NIF = $nif;
        return $this;
    }

    public function getIDOtro(): ?IDOtro
    {
        return $this->IDOtro;
    }

    public function setIDOtro(?IDOtro $idOtro): self
    {
        if ($idOtro !== null && $this->NIF !== null) {
            throw new \InvalidArgumentException('Cannot set both NIF and IDOtro');
        }
        $this->IDOtro = $idOtro;
        return $this;
    }
} 
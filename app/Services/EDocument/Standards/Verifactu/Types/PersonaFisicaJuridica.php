<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class PersonaFisicaJuridica
{
    /** @var string */
    #[SerializedName('sum1:NombreRazon')]
    protected $NombreRazon;

    /** @var string|null */
    #[SerializedName('sum1:NIF')]
    protected $NIF;

    /** @var IDOtro|null */
    #[SerializedName('sum1:IDOtro')]
    protected $IDOtro;

    public function getNombreRazon(): string
    {
        return $this->NombreRazon;
    }

    public function setNombreRazon(string $nombreRazon): self
    {
        if (strlen($nombreRazon) > 120) {
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
        if ($nif !== null) {
            if (!preg_match('/^[A-Z0-9]{9}$/', $nif)) {
                throw new \InvalidArgumentException('NIF must be a valid NIF (9 alphanumeric characters)');
            }
            $this->NIF = $nif;
            $this->IDOtro = null; // Clear IDOtro as it's a choice
        }
        return $this;
    }

    public function getIDOtro(): ?IDOtro
    {
        return $this->IDOtro;
    }

    public function setIDOtro(?IDOtro $idOtro): self
    {
        if ($idOtro !== null) {
            $this->IDOtro = $idOtro;
            $this->NIF = null; // Clear NIF as it's a choice
        }
        return $this;
    }
}
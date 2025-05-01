<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDDestinatario extends PersonaFisicaJuridicaES
{
    /** @var string|null */
    protected $CodigoPais;

    /** @var IDOtro|null */
    protected $IDOtro;

    public function getCodigoPais(): ?string
    {
        return $this->CodigoPais;
    }

    public function setCodigoPais(?string $codigoPais): self
    {
        if ($codigoPais !== null && strlen($codigoPais) !== 2) {
            throw new \InvalidArgumentException('CodigoPais must be a 2-character ISO country code');
        }
        $this->CodigoPais = $codigoPais;
        return $this;
    }

    public function getIDOtro(): ?IDOtro
    {
        return $this->IDOtro;
    }

    public function setIDOtro(?IDOtro $idOtro): self
    {
        $this->IDOtro = $idOtro;
        return $this;
    }
} 
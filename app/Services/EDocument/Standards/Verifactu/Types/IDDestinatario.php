<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDDestinatario extends PersonaFisicaJuridicaES
{
    /** @var string|null */
    protected $codigoPais;

    /** @var IDOtro|null */
    protected $idOtro;

    public function getCodigoPais(): ?string
    {
        return $this->codigoPais;
    }

    public function setCodigoPais(?string $codigoPais): self
    {
        if ($codigoPais !== null && strlen($codigoPais) !== 2) {
            throw new \InvalidArgumentException('CodigoPais must be a 2-character ISO country code');
        }
        $this->codigoPais = $codigoPais;
        return $this;
    }

    public function getIdOtro(): ?IDOtro
    {
        return $this->idOtro;
    }

    public function setIdOtro(?IDOtro $idOtro): self
    {
        $this->idOtro = $idOtro;
        return $this;
    }
} 
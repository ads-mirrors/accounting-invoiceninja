<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class IDDestinatario extends PersonaFisicaJuridicaES
{
    /** @var string|null */
    #[SerializedName('sum1:CodigoPais')]
    protected $CodigoPais;

    /** @var IDOtro|null */
    #[SerializedName('sum1:IDOtro')]
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
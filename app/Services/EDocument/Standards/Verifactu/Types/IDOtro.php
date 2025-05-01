<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class IDOtro
{
    /** @var string */
    #[SerializedName('sum1:CodigoPais')]
    protected $CodigoPais;

    /** @var string */
    #[SerializedName('sum1:IDType')]
    protected $IDType;

    /** @var string */
    #[SerializedName('sum1:ID')]
    protected $ID;

    public function getCodigoPais(): string
    {
        return $this->CodigoPais;
    }

    public function setCodigoPais(string $codigoPais): self
    {
        if (strlen($codigoPais) !== 2) {
            throw new \InvalidArgumentException('CodigoPais must be a 2-character ISO country code');
        }
        $this->CodigoPais = $codigoPais;
        return $this;
    }

    public function getIDType(): string
    {
        return $this->IDType;
    }

    public function setIDType(string $idType): self
    {
        $validTypes = ['02', '03', '04', '05', '06', '07'];
        if (!in_array($idType, $validTypes)) {
            throw new \InvalidArgumentException('Invalid IDType value');
        }
        $this->IDType = $idType;
        return $this;
    }

    public function getID(): string
    {
        return $this->ID;
    }

    public function setID(string $id): self
    {
        if (strlen($id) > 20) {
            throw new \InvalidArgumentException('ID must not exceed 20 characters');
        }
        $this->ID = $id;
        return $this;
    }
} 
<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDOtro
{
    /** @var string */
    protected $CodigoPais;

    /** @var string */
    protected $IDType;

    /** @var string */
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
        if (!in_array($idType, ['02', '03', '04', '05', '06', '07'])) {
            throw new \InvalidArgumentException('Invalid IDType value');
        }
        if ($this->CodigoPais === 'ES' && $idType === '01') {
            throw new \InvalidArgumentException('IDType 01 cannot be used with CodigoPais ES');
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
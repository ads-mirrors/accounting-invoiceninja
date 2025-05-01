<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class IDOtro
{
    /** @var string|null ISO 3166-1 alpha-2 */
    protected $codigoPais;

    /** @var string */
    protected $idType;

    /** @var string Max length 20 characters */
    protected $id;

    public function getCodigoPais(): ?string
    {
        return $this->codigoPais;
    }

    public function setCodigoPais(?string $codigoPais): self
    {
        if ($codigoPais !== null) {
            if (strlen($codigoPais) !== 2) {
                throw new \InvalidArgumentException('CodigoPais must be a 2-letter ISO country code');
            }
            // Prevent using ES with IDType 01
            if ($codigoPais === 'ES' && $this->idType === '01') {
                throw new \InvalidArgumentException('Cannot use CodigoPais=ES with IDType=01, use NIF instead');
            }
        }
        $this->codigoPais = $codigoPais;
        return $this;
    }

    public function getIdType(): string
    {
        return $this->idType;
    }

    public function setIdType(string $idType): self
    {
        // Prevent using ES with IDType 01
        if ($this->codigoPais === 'ES' && $idType === '01') {
            throw new \InvalidArgumentException('Cannot use CodigoPais=ES with IDType=01, use NIF instead');
        }
        $this->idType = $idType;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        if (strlen($id) > 20) {
            throw new \InvalidArgumentException('ID must not exceed 20 characters');
        }
        $this->id = $id;
        return $this;
    }
} 
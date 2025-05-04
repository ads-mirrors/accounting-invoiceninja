<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class PersonaFisicaJuridica
{
    /** @var string|null */
    #[SerializedName('sum1:TipoPersona')]
    protected $TipoPersona;

    /** @var string */
    #[SerializedName('sum1:NIF')]
    protected $NIF;

    /** @var string|null */
    #[SerializedName('sum1:IDOtro')]
    protected $IDOtro;

    /** @var string|null */
    #[SerializedName('sum1:CodigoPais')]
    protected $CodigoPais;

    /** @var string|null */
    #[SerializedName('sum1:IDType')]
    protected $IDType;

    /** @var string|null */
    #[SerializedName('sum1:ID')]
    protected $ID;

    /** @var string|null */
    #[SerializedName('sum1:Web')]
    protected $Web;

    public function getTipoPersona(): ?string
    {
        return $this->TipoPersona;
    }

    public function setTipoPersona(?string $tipoPersona): self
    {
        if ($tipoPersona !== null && !in_array($tipoPersona, ['F', 'J'])) {
            throw new \InvalidArgumentException('TipoPersona must be either "F" (Física) or "J" (Jurídica)');
        }
        $this->TipoPersona = $tipoPersona;
        return $this;
    }

    public function getNIF(): string
    {
        return $this->NIF;
    }

    public function setNIF(string $nif): self
    {
        if (!preg_match('/^[A-Z0-9]{9}$/', $nif)) {
            throw new \InvalidArgumentException('NIF must be a valid NIF (9 alphanumeric characters)');
        }
        $this->NIF = $nif;
        return $this;
    }

    public function getIDOtro(): ?string
    {
        return $this->IDOtro;
    }

    public function setIDOtro(?string $idOtro): self
    {
        if ($idOtro !== null && strlen($idOtro) > 20) {
            throw new \InvalidArgumentException('IDOtro must not exceed 20 characters');
        }
        $this->IDOtro = $idOtro;
        return $this;
    }

    public function getCodigoPais(): ?string
    {
        return $this->CodigoPais;
    }

    public function setCodigoPais(?string $codigoPais): self
    {
        if ($codigoPais !== null && !preg_match('/^[A-Z]{2}$/', $codigoPais)) {
            throw new \InvalidArgumentException('CodigoPais must be a 2-letter ISO country code');
        }
        $this->CodigoPais = $codigoPais;
        return $this;
    }

    public function getIDType(): ?string
    {
        return $this->IDType;
    }

    public function setIDType(?string $idType): self
    {
        if ($idType !== null && !in_array($idType, ['02', '03', '04', '05', '06', '07'])) {
            throw new \InvalidArgumentException('IDType must be one of: 02 (NIF-IVA), 03 (Pasaporte), 04 (Doc oficial país residencia), 05 (Cert residencia), 06 (Otro doc probatorio), 07 (No censado))');
        }
        $this->IDType = $idType;
        return $this;
    }

    public function getID(): ?string
    {
        return $this->ID;
    }

    public function setID(?string $id): self
    {
        if ($id !== null && strlen($id) > 20) {
            throw new \InvalidArgumentException('ID must not exceed 20 characters');
        }
        $this->ID = $id;
        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->Web;
    }

    public function setWeb(?string $web): self
    {
        if ($web !== null && strlen($web) > 500) {
            throw new \InvalidArgumentException('Web must not exceed 500 characters');
        }
        $this->Web = $web;
        return $this;
    }
} 
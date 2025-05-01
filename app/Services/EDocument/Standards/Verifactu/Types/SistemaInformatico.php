<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class SistemaInformatico
{
    /** @var string Max length 120 characters */
    protected $nombreRazon;

    /** @var string|null NIF format */
    protected $nif;

    /** @var array|null */
    protected $idOtro;

    /** @var string Max length 30 characters */
    protected $nombreSistemaInformatico;

    /** @var string Max length 2 characters */
    protected $idSistemaInformatico;

    /** @var string Max length 50 characters */
    protected $version;

    /** @var string Max length 100 characters */
    protected $numeroInstalacion;

    /** @var string 'S' or 'N' */
    protected $tipoUsoPosibleSoloVerifactu;

    /** @var string 'S' or 'N' */
    protected $tipoUsoPosibleMultiOT;

    /** @var string 'S' or 'N' */
    protected $indicadorMultiplesOT;

    public function getNombreRazon(): string
    {
        return $this->nombreRazon;
    }

    public function setNombreRazon(string $nombreRazon): self
    {
        if (strlen($nombreRazon) > 120) {
            throw new \InvalidArgumentException('NombreRazon must not exceed 120 characters');
        }
        $this->nombreRazon = $nombreRazon;
        return $this;
    }

    public function getNif(): ?string
    {
        return $this->nif;
    }

    public function setNif(?string $nif): self
    {
        // TODO: Add NIF validation
        $this->nif = $nif;
        return $this;
    }

    public function getIdOtro(): ?array
    {
        return $this->idOtro;
    }

    public function setIdOtro(?array $idOtro): self
    {
        $this->idOtro = $idOtro;
        return $this;
    }

    public function getNombreSistemaInformatico(): string
    {
        return $this->nombreSistemaInformatico;
    }

    public function setNombreSistemaInformatico(string $nombreSistemaInformatico): self
    {
        if (strlen($nombreSistemaInformatico) > 30) {
            throw new \InvalidArgumentException('NombreSistemaInformatico must not exceed 30 characters');
        }
        $this->nombreSistemaInformatico = $nombreSistemaInformatico;
        return $this;
    }

    public function getIdSistemaInformatico(): string
    {
        return $this->idSistemaInformatico;
    }

    public function setIdSistemaInformatico(string $idSistemaInformatico): self
    {
        if (strlen($idSistemaInformatico) > 2) {
            throw new \InvalidArgumentException('IdSistemaInformatico must not exceed 2 characters');
        }
        $this->idSistemaInformatico = $idSistemaInformatico;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        if (strlen($version) > 50) {
            throw new \InvalidArgumentException('Version must not exceed 50 characters');
        }
        $this->version = $version;
        return $this;
    }

    public function getNumeroInstalacion(): string
    {
        return $this->numeroInstalacion;
    }

    public function setNumeroInstalacion(string $numeroInstalacion): self
    {
        if (strlen($numeroInstalacion) > 100) {
            throw new \InvalidArgumentException('NumeroInstalacion must not exceed 100 characters');
        }
        $this->numeroInstalacion = $numeroInstalacion;
        return $this;
    }

    public function getTipoUsoPosibleSoloVerifactu(): string
    {
        return $this->tipoUsoPosibleSoloVerifactu;
    }

    public function setTipoUsoPosibleSoloVerifactu(string $value): self
    {
        if (!in_array($value, ['S', 'N'])) {
            throw new \InvalidArgumentException('TipoUsoPosibleSoloVerifactu must be either "S" or "N"');
        }
        $this->tipoUsoPosibleSoloVerifactu = $value;
        return $this;
    }

    public function getTipoUsoPosibleMultiOT(): string
    {
        return $this->tipoUsoPosibleMultiOT;
    }

    public function setTipoUsoPosibleMultiOT(string $value): self
    {
        if (!in_array($value, ['S', 'N'])) {
            throw new \InvalidArgumentException('TipoUsoPosibleMultiOT must be either "S" or "N"');
        }
        $this->tipoUsoPosibleMultiOT = $value;
        return $this;
    }

    public function getIndicadorMultiplesOT(): string
    {
        return $this->indicadorMultiplesOT;
    }

    public function setIndicadorMultiplesOT(string $value): self
    {
        if (!in_array($value, ['S', 'N'])) {
            throw new \InvalidArgumentException('IndicadorMultiplesOT must be either "S" or "N"');
        }
        $this->indicadorMultiplesOT = $value;
        return $this;
    }
} 
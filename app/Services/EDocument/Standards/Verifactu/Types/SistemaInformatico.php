<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class SistemaInformatico extends PersonaFisicaJuridicaES
{
    /** @var string */
    protected $NombreSistemaInformatico;

    /** @var string */
    protected $IdSistemaInformatico;

    /** @var string */
    protected $Version;

    /** @var string */
    protected $NumeroInstalacion;

    /** @var string */
    protected $TipoUsoPosibleSoloVerifactu;

    /** @var string */
    protected $TipoUsoPosibleMultiOT;

    /** @var string */
    protected $IndicadorMultiplesOT;

    public function getNombreSistemaInformatico(): string
    {
        return $this->NombreSistemaInformatico;
    }

    public function setNombreSistemaInformatico(string $nombreSistemaInformatico): self
    {
        if (strlen($nombreSistemaInformatico) > 120) {
            throw new \InvalidArgumentException('NombreSistemaInformatico must not exceed 120 characters');
        }
        $this->NombreSistemaInformatico = $nombreSistemaInformatico;
        return $this;
    }

    public function getIdSistemaInformatico(): string
    {
        return $this->IdSistemaInformatico;
    }

    public function setIdSistemaInformatico(string $idSistemaInformatico): self
    {
        if (strlen($idSistemaInformatico) > 20) {
            throw new \InvalidArgumentException('IdSistemaInformatico must not exceed 20 characters');
        }
        $this->IdSistemaInformatico = $idSistemaInformatico;
        return $this;
    }

    public function getVersion(): string
    {
        return $this->Version;
    }

    public function setVersion(string $version): self
    {
        if (strlen($version) > 20) {
            throw new \InvalidArgumentException('Version must not exceed 20 characters');
        }
        $this->Version = $version;
        return $this;
    }

    public function getNumeroInstalacion(): string
    {
        return $this->NumeroInstalacion;
    }

    public function setNumeroInstalacion(string $numeroInstalacion): self
    {
        if (strlen($numeroInstalacion) > 20) {
            throw new \InvalidArgumentException('NumeroInstalacion must not exceed 20 characters');
        }
        $this->NumeroInstalacion = $numeroInstalacion;
        return $this;
    }

    public function getTipoUsoPosibleSoloVerifactu(): string
    {
        return $this->TipoUsoPosibleSoloVerifactu;
    }

    public function setTipoUsoPosibleSoloVerifactu(string $tipoUsoPosibleSoloVerifactu): self
    {
        if (!in_array($tipoUsoPosibleSoloVerifactu, ['S', 'N'])) {
            throw new \InvalidArgumentException('TipoUsoPosibleSoloVerifactu must be either "S" or "N"');
        }
        $this->TipoUsoPosibleSoloVerifactu = $tipoUsoPosibleSoloVerifactu;
        return $this;
    }

    public function getTipoUsoPosibleMultiOT(): string
    {
        return $this->TipoUsoPosibleMultiOT;
    }

    public function setTipoUsoPosibleMultiOT(string $tipoUsoPosibleMultiOT): self
    {
        if (!in_array($tipoUsoPosibleMultiOT, ['S', 'N'])) {
            throw new \InvalidArgumentException('TipoUsoPosibleMultiOT must be either "S" or "N"');
        }
        $this->TipoUsoPosibleMultiOT = $tipoUsoPosibleMultiOT;
        return $this;
    }

    public function getIndicadorMultiplesOT(): string
    {
        return $this->IndicadorMultiplesOT;
    }

    public function setIndicadorMultiplesOT(string $indicadorMultiplesOT): self
    {
        if (!in_array($indicadorMultiplesOT, ['S', 'N'])) {
            throw new \InvalidArgumentException('IndicadorMultiplesOT must be either "S" or "N"');
        }
        $this->IndicadorMultiplesOT = $indicadorMultiplesOT;
        return $this;
    }
} 
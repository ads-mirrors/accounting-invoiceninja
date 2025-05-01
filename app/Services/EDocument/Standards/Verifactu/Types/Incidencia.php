<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Incidencia
{
    /** @var string */
    protected $Codigo;

    /** @var string */
    protected $Descripcion;

    /** @var string|null Max length 120 characters */
    protected $NombreRazon;

    /** @var string|null NIF format */
    protected $NIF;

    /** @var string|null */
    protected $FechaHora;

    public function getCodigo(): string
    {
        return $this->Codigo;
    }

    public function setCodigo(string $codigo): self
    {
        if (!preg_match('/^\d{3}$/', $codigo)) {
            throw new \InvalidArgumentException('Codigo must be a 3-digit number');
        }
        $this->Codigo = $codigo;
        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->Descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        if (strlen($descripcion) > 500) {
            throw new \InvalidArgumentException('Descripcion must not exceed 500 characters');
        }
        $this->Descripcion = $descripcion;
        return $this;
    }

    public function getNombreRazon(): ?string
    {
        return $this->NombreRazon;
    }

    public function setNombreRazon(?string $nombreRazon): self
    {
        if ($nombreRazon !== null && strlen($nombreRazon) > 120) {
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
        // TODO: Add NIF validation
        $this->NIF = $nif;
        return $this;
    }

    public function getFechaHora(): ?string
    {
        return $this->FechaHora;
    }

    public function setFechaHora(?string $fechaHora): self
    {
        if ($fechaHora !== null) {
            if (!\DateTime::createFromFormat('Y-m-d H:i:s', $fechaHora)) {
                throw new \InvalidArgumentException('FechaHora must be in YYYY-MM-DD HH:mm:ss format');
            }
        }
        $this->FechaHora = $fechaHora;
        return $this;
    }
} 
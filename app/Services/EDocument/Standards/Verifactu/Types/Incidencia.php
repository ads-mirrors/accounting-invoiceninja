<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Incidencia
{
    /** @var string */
    protected $codigo;

    /** @var string */
    protected $descripcion;

    /** @var string|null Max length 120 characters */
    protected $nombreRazon;

    /** @var string|null NIF format */
    protected $nif;

    /** @var string|null */
    protected $fechaHora;

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function setCodigo(string $codigo): self
    {
        if (!preg_match('/^\d{3}$/', $codigo)) {
            throw new \InvalidArgumentException('Codigo must be a 3-digit number');
        }
        $this->codigo = $codigo;
        return $this;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        if (strlen($descripcion) > 500) {
            throw new \InvalidArgumentException('Descripcion must not exceed 500 characters');
        }
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getNombreRazon(): ?string
    {
        return $this->nombreRazon;
    }

    public function setNombreRazon(?string $nombreRazon): self
    {
        if ($nombreRazon !== null && strlen($nombreRazon) > 120) {
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

    public function getFechaHora(): ?string
    {
        return $this->fechaHora;
    }

    public function setFechaHora(?string $fechaHora): self
    {
        if ($fechaHora !== null) {
            if (!\DateTime::createFromFormat('Y-m-d H:i:s', $fechaHora)) {
                throw new \InvalidArgumentException('FechaHora must be in YYYY-MM-DD HH:mm:ss format');
            }
        }
        $this->fechaHora = $fechaHora;
        return $this;
    }
} 
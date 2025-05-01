<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class ObligadoEmision extends PersonaFisicaJuridicaES
{
    /** @var string|null */
    protected $TipoPersona;

    /** @var string|null */
    protected $RazonSocialCompleta;

    /** @var string|null */
    protected $NombreComercial;

    /** @var string|null */
    protected $CodigoPostal;

    /** @var string|null */
    protected $Direccion;

    /** @var string|null */
    protected $Poblacion;

    /** @var string|null */
    protected $Provincia;

    /** @var string|null */
    protected $Pais;

    /** @var string|null */
    protected $Telefono;

    /** @var string|null */
    protected $Email;

    /** @var string|null */
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

    public function getRazonSocialCompleta(): ?string
    {
        return $this->RazonSocialCompleta;
    }

    public function setRazonSocialCompleta(?string $razonSocialCompleta): self
    {
        if ($razonSocialCompleta !== null && strlen($razonSocialCompleta) > 120) {
            throw new \InvalidArgumentException('RazonSocialCompleta must not exceed 120 characters');
        }
        $this->RazonSocialCompleta = $razonSocialCompleta;
        return $this;
    }

    public function getNombreComercial(): ?string
    {
        return $this->NombreComercial;
    }

    public function setNombreComercial(?string $nombreComercial): self
    {
        if ($nombreComercial !== null && strlen($nombreComercial) > 120) {
            throw new \InvalidArgumentException('NombreComercial must not exceed 120 characters');
        }
        $this->NombreComercial = $nombreComercial;
        return $this;
    }

    public function getCodigoPostal(): ?string
    {
        return $this->CodigoPostal;
    }

    public function setCodigoPostal(?string $codigoPostal): self
    {
        if ($codigoPostal !== null && strlen($codigoPostal) > 10) {
            throw new \InvalidArgumentException('CodigoPostal must not exceed 10 characters');
        }
        $this->CodigoPostal = $codigoPostal;
        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->Direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        if ($direccion !== null && strlen($direccion) > 250) {
            throw new \InvalidArgumentException('Direccion must not exceed 250 characters');
        }
        $this->Direccion = $direccion;
        return $this;
    }

    public function getPoblacion(): ?string
    {
        return $this->Poblacion;
    }

    public function setPoblacion(?string $poblacion): self
    {
        if ($poblacion !== null && strlen($poblacion) > 50) {
            throw new \InvalidArgumentException('Poblacion must not exceed 50 characters');
        }
        $this->Poblacion = $poblacion;
        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->Provincia;
    }

    public function setProvincia(?string $provincia): self
    {
        if ($provincia !== null && strlen($provincia) > 20) {
            throw new \InvalidArgumentException('Provincia must not exceed 20 characters');
        }
        $this->Provincia = $provincia;
        return $this;
    }

    public function getPais(): ?string
    {
        return $this->Pais;
    }

    public function setPais(?string $pais): self
    {
        if ($pais !== null && strlen($pais) > 20) {
            throw new \InvalidArgumentException('Pais must not exceed 20 characters');
        }
        $this->Pais = $pais;
        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->Telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        if ($telefono !== null && strlen($telefono) > 20) {
            throw new \InvalidArgumentException('Telefono must not exceed 20 characters');
        }
        $this->Telefono = $telefono;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->Email;
    }

    public function setEmail(?string $email): self
    {
        if ($email !== null) {
            if (strlen($email) > 120) {
                throw new \InvalidArgumentException('Email must not exceed 120 characters');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }
        }
        $this->Email = $email;
        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->Web;
    }

    public function setWeb(?string $web): self
    {
        if ($web !== null && strlen($web) > 250) {
            throw new \InvalidArgumentException('Web must not exceed 250 characters');
        }
        $this->Web = $web;
        return $this;
    }
} 
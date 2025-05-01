<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class ObligadoEmision extends PersonaFisicaJuridicaES
{
    /** @var string|null */
    protected $tipoPersona;

    /** @var string|null */
    protected $razonSocialCompleta;

    /** @var string|null */
    protected $nombreComercial;

    /** @var string|null */
    protected $codigoPostal;

    /** @var string|null */
    protected $direccion;

    /** @var string|null */
    protected $poblacion;

    /** @var string|null */
    protected $provincia;

    /** @var string|null */
    protected $pais;

    /** @var string|null */
    protected $telefono;

    /** @var string|null */
    protected $email;

    /** @var string|null */
    protected $web;

    public function getTipoPersona(): ?string
    {
        return $this->tipoPersona;
    }

    public function setTipoPersona(?string $tipoPersona): self
    {
        if ($tipoPersona !== null && !in_array($tipoPersona, ['F', 'J'])) {
            throw new \InvalidArgumentException('TipoPersona must be either "F" (Física) or "J" (Jurídica)');
        }
        $this->tipoPersona = $tipoPersona;
        return $this;
    }

    public function getRazonSocialCompleta(): ?string
    {
        return $this->razonSocialCompleta;
    }

    public function setRazonSocialCompleta(?string $razonSocialCompleta): self
    {
        if ($razonSocialCompleta !== null && strlen($razonSocialCompleta) > 120) {
            throw new \InvalidArgumentException('RazonSocialCompleta must not exceed 120 characters');
        }
        $this->razonSocialCompleta = $razonSocialCompleta;
        return $this;
    }

    public function getNombreComercial(): ?string
    {
        return $this->nombreComercial;
    }

    public function setNombreComercial(?string $nombreComercial): self
    {
        if ($nombreComercial !== null && strlen($nombreComercial) > 120) {
            throw new \InvalidArgumentException('NombreComercial must not exceed 120 characters');
        }
        $this->nombreComercial = $nombreComercial;
        return $this;
    }

    public function getCodigoPostal(): ?string
    {
        return $this->codigoPostal;
    }

    public function setCodigoPostal(?string $codigoPostal): self
    {
        if ($codigoPostal !== null && strlen($codigoPostal) > 10) {
            throw new \InvalidArgumentException('CodigoPostal must not exceed 10 characters');
        }
        $this->codigoPostal = $codigoPostal;
        return $this;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function setDireccion(?string $direccion): self
    {
        if ($direccion !== null && strlen($direccion) > 250) {
            throw new \InvalidArgumentException('Direccion must not exceed 250 characters');
        }
        $this->direccion = $direccion;
        return $this;
    }

    public function getPoblacion(): ?string
    {
        return $this->poblacion;
    }

    public function setPoblacion(?string $poblacion): self
    {
        if ($poblacion !== null && strlen($poblacion) > 50) {
            throw new \InvalidArgumentException('Poblacion must not exceed 50 characters');
        }
        $this->poblacion = $poblacion;
        return $this;
    }

    public function getProvincia(): ?string
    {
        return $this->provincia;
    }

    public function setProvincia(?string $provincia): self
    {
        if ($provincia !== null && strlen($provincia) > 20) {
            throw new \InvalidArgumentException('Provincia must not exceed 20 characters');
        }
        $this->provincia = $provincia;
        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(?string $pais): self
    {
        if ($pais !== null && strlen($pais) > 20) {
            throw new \InvalidArgumentException('Pais must not exceed 20 characters');
        }
        $this->pais = $pais;
        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        if ($telefono !== null && strlen($telefono) > 20) {
            throw new \InvalidArgumentException('Telefono must not exceed 20 characters');
        }
        $this->telefono = $telefono;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
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
        $this->email = $email;
        return $this;
    }

    public function getWeb(): ?string
    {
        return $this->web;
    }

    public function setWeb(?string $web): self
    {
        if ($web !== null && strlen($web) > 250) {
            throw new \InvalidArgumentException('Web must not exceed 250 characters');
        }
        $this->web = $web;
        return $this;
    }
} 
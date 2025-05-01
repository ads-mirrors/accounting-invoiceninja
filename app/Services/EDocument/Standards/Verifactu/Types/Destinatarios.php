<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

class Destinatarios
{
    /** @var array<IDDestinatario> */
    protected $IDDestinatario = [];

    /**
     * @return array<IDDestinatario>
     */
    public function getIDDestinatario(): array
    {
        return $this->IDDestinatario;
    }

    public function addIDDestinatario(IDDestinatario $destinatario): self
    {
        $this->IDDestinatario[] = $destinatario;
        return $this;
    }

    /**
     * @param array<IDDestinatario> $destinatarios
     */
    public function setIDDestinatario(array $destinatarios): self
    {
        $this->IDDestinatario = $destinatarios;
        return $this;
    }
} 
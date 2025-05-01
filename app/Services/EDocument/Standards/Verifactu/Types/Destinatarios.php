<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Destinatarios
{
    /** @var array<IDDestinatario> */
    #[SerializedName('sum1:IDDestinatario')]
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
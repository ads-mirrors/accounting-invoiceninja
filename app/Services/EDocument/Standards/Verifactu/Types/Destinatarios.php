<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Destinatarios
{
    /** @var IDDestinatario[] */
    #[SerializedName('sum1:IDDestinatario')]
    protected $IDDestinatario = [];

    /**
     * @return IDDestinatario[]
     */
    public function getIDDestinatario(): array
    {
        return $this->IDDestinatario;
    }

    public function addIDDestinatario(IDDestinatario $idDestinatario): self
    {
        if (count($this->IDDestinatario) >= 1000) {
            throw new \InvalidArgumentException('Maximum number of IDDestinatario (1000) exceeded');
        }
        $this->IDDestinatario[] = $idDestinatario;
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
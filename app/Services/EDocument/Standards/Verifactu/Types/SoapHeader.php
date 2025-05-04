<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class SoapHeader
{
    /** @var string|null */
    #[SerializedName('soapenv:Action')]
    protected $Action;

    /** @var string|null */
    #[SerializedName('soapenv:MessageID')]
    protected $MessageID;

    /** @var string|null */
    #[SerializedName('soapenv:To')]
    protected $To;

    public function getAction(): ?string
    {
        return $this->Action;
    }

    public function setAction(?string $action): self
    {
        $this->Action = $action;
        return $this;
    }

    public function getMessageID(): ?string
    {
        return $this->MessageID;
    }

    public function setMessageID(?string $messageID): self
    {
        $this->MessageID = $messageID;
        return $this;
    }

    public function getTo(): ?string
    {
        return $this->To;
    }

    public function setTo(?string $to): self
    {
        $this->To = $to;
        return $this;
    }
}

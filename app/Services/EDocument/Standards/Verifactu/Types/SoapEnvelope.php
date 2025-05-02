<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class SoapEnvelope
{
        /** @var SoapHeader */
    #[SerializedName('soapenv:Header')]
    protected $Header;

    /** @var SoapBody */
    #[SerializedName('soapenv:Body')]
    protected $Body;

    public function getHeader(): SoapHeader
    {
        return $this->Header;
    }

    public function setHeader(SoapHeader $header): self
    {
        $this->Header = $header;
        return $this;
    }

    public function getBody(): SoapBody
    {
        return $this->Body;
    }

    public function setBody(SoapBody $body): self
    {
        $this->Body = $body;
        return $this;
    }
}

<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\XmlRoot;
use Symfony\Component\Serializer\Annotation\SerializedName;
use App\Services\EDocument\Standards\Verifactu\Types\SoapBody;
use App\Services\EDocument\Standards\Verifactu\Types\SoapHeader;

class SoapEnvelope
{
    #[SerializedName('@xmlns:soapenv')]
    public $xmlns_soapenv = 'http://schemas.xmlsoap.org/soap/envelope/';

    #[SerializedName('@xmlns:sum')]
    public $xmlns_sum = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd';

    #[SerializedName('@xmlns:sum1')]
    public $xmlns_sum1 = 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd';

    #[SerializedName('@xmlns:xd')]
    public $xmlns_xd = 'http://www.w3.org/2000/09/xmldsig#';
    
    /** @var SoapHeader */
    #[SerializedName('soapenv:Header')]
    protected $Header;

    /** @var SoapBody */
    #[SerializedName('soapenv:Body')]
    protected $Body;

    public function getHeader(): ?SoapHeader
    {
        return $this->Header;
    }

    public function setHeader(SoapHeader $header): self
    {
        $this->Header = $header;
        return $this;
    }

    public function getBody(): ?SoapBody
    {
        return $this->Body;
    }

    public function setBody(SoapBody $body): self
    {
        $this->Body = $body;
        return $this;
    }
}

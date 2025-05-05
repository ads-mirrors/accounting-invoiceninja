<?php

namespace App\Services\EDocument\Standards\Verifactu\Types;

use Symfony\Component\Serializer\Annotation\SerializedName;

class IDOtro
{

    // 01	NIFContraparte	Spanish Tax ID (NIF) of the counterparty	NIF de la contraparte (solo válido con NIF, no en IDOtro)
    // 02	VATNumber	EU VAT Number	Número de IVA de operadores intracomunitarios
    // 03	Passport/Foreign ID	National ID, passport, or similar from non-EU countries	Documento oficial de identificación expedido por otro país
    // 04	Legal Entity ID	Tax ID for foreign legal entities	Código de identificación fiscal de personas jurídicas extranjeras
    // 05	Residence Cert.	Certificate of residence issued by a tax authority	Certificado de residencia fiscal
    // 06	Other	Other officially recognized identifier	Otro documento reconocido oficialmente
    public array $id_types = [
        '01',
        '02',
        '03',
        '04',
        '05',
        '06',
    ];

    /** @var string */
    #[SerializedName('sum1:CodigoPais')] // iso 2 country code
    protected $CodigoPais;

    /** @var string */
    #[SerializedName('sum1:IDType')]
    protected $IDType;

    /** @var string */
    #[SerializedName('sum1:ID')]
    protected $ID;

    public function getCodigoPais(): string
    {
        return $this->CodigoPais;
    }

    public function setCodigoPais(string $codigoPais): self
    {
        if (strlen($codigoPais) !== 2) {
            throw new \InvalidArgumentException('CodigoPais must be a 2-character ISO country code');
        }
        $this->CodigoPais = $codigoPais;
        return $this;
    }

    public function getIDType(): string
    {
        return $this->IDType;
    }

    public function setIDType(string $idType): self
    {
        $validTypes = ['02', '03', '04', '05', '06', '07'];
        if (!in_array($idType, $validTypes)) {
            throw new \InvalidArgumentException('Invalid IDType value');
        }
        $this->IDType = $idType;
        return $this;
    }

    public function getID(): string
    {
        return $this->ID;
    }

    public function setID(string $id): self
    {
        if (strlen($id) > 20) {
            throw new \InvalidArgumentException('ID must not exceed 20 characters');
        }
        $this->ID = $id;
        return $this;
    }
} 
<?php

namespace App\Services\EDocument\Standards;

use Carbon\Carbon;
use App\Models\Invoice;
use BaconQrCode\Writer;
use App\Services\AbstractService;
use BaconQrCode\Renderer\ImageRenderer;
use Symfony\Component\Serializer\Serializer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Services\EDocument\Standards\Verifactu\Types\Cabecera;
use App\Services\EDocument\Standards\Verifactu\Types\SoapBody;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use App\Services\EDocument\Standards\Verifactu\VerifactuClient;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Types\SoapEnvelope;
use App\Services\EDocument\Standards\Verifactu\Types\ObligadoEmision;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use App\Services\EDocument\Standards\Verifactu\InvoiceninjaToVerifactuMapper;
use App\Services\EDocument\Standards\Verifactu\Types\RegFactuSistemaFacturacion;

class Verifactu 
{
    public function __construct(private Invoice $invoice)
    {
    }

    public function send(mixed $registro)
    {

        $client = new VerifactuClient(VerifactuClient::MODE_PROD);
        $response = $client->sendRegistroAlta($this->getRegistroAlta());

        var_dump($response);

    }

    public function getRegistroAlta(): string
    {
        $mapper = new InvoiceninjaToVerifactuMapper();
        $regAlta = $mapper->mapRegistroAlta($this->invoice);

        $soapEnvelope = new SoapEnvelope();
        $soapBody = new SoapBody();
        
        $RegFactuSistemaFacturacion = new RegFactuSistemaFacturacion();
        
        //The User or Corp that generated AND sends the invoice (ie Invoice Ninja!)
        $cabecera = new Cabecera();
        $obligadoEmision = new ObligadoEmision();
        $obligadoEmision->setNombreRazon($this->invoice->company->present()->name());
        $obligadoEmision->setNIF($this->invoice->company->settings->vat_number);
        $cabecera->setObligadoEmision($obligadoEmision);
        $RegFactuSistemaFacturacion->setCabecera($cabecera);

        //The invoice itself
        $RegFactuSistemaFacturacion->setRegistroAlta($regAlta);

        $soapBody->setRegFactuSistemaFacturacion($RegFactuSistemaFacturacion);
        $soapEnvelope->setBody($soapBody);

        return $this->serializeXml($soapEnvelope);
    }
    
    public function serializeXml(SoapEnvelope $registro): string
    {
        
        $serializer = $this->getSerializer();

        $context = [
            \Symfony\Component\Serializer\Normalizer\DateTimeNormalizer::FORMAT_KEY => 'd-m-Y', 
            \Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true
        ];

        $object = $serializer->normalize($registro, null, [\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $object = $this->removeEmptyValues($object);

        $data = $serializer->encode($object, 'xml', $context);

        $data = str_replace(['<response>','</response>'], '', $data);
        $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $data);

        return $data;

    }

    /**
     * getQrCode
     * 
     * @return string
     */
    public function getQrCode(): string
    {
        //testmode
        $base_url = 'https://prewww2.aeat.es/wlpl/TIKE-CONT/ValidarQR';
        
        //production
        // $base_url = https://www2.agenciatributaria.gob.es/wlpl/TIKE-CONT/ValidarQR

        // Format date to dd-mm-yyyy
        $fecha = \Carbon\Carbon::parse($this->invoice->date)->format('d-m-Y');
        
        // Format amount to 2 decimal places without thousands separator
        $importe = number_format($this->invoice->amount, 2, '.', '');

        // NIF is required for the QR code, if no NIF is present we use 00000000H
        if(strlen($this->invoice->client->vat_number) > 2 && $this->invoice->client->country->iso_3166_2 === 'ES') {
            $nif = $this->invoice->client->vat_number;
        } else {
            $nif = '00000000H'; //unknown / foreign client
        }

        $params = [
            'nif' => $nif,
            'numserie' => $this->invoice->number,
            'fecha' => $fecha,
            'importe' => $importe,
        ];

        // Build URL with properly encoded parameters
        $query = http_build_query($params);
         
        try {
            $renderer = new ImageRenderer(
                new RendererStyle(200),
                new SvgImageBackEnd()
            );
            $writer = new Writer($renderer);

            $qr = $writer->writeString($base_url . '?' . $query, 'utf-8');

            return htmlspecialchars("<svg viewBox='0 0 200 200' width='200' height='200' x='0' y='0' xmlns='http://www.w3.org/2000/svg'>
                <rect x='0' y='0' width='100%'' height='100%' />{$qr}</svg>");

        } catch (\Throwable $e) {
            nlog(" QR failure => ".$e->getMessage());
            return '';
        }

    }
    
    /**
     * getSerializer
     *
     * Returns the Symfony Serializer
     * 
     * @return Serializer
     */
    public function getSerializer(): Serializer
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $typeExtractors = [$reflectionExtractor, $phpDocExtractor];
        $descriptionExtractors = [$phpDocExtractor];
        $propertyInitializableExtractors = [$reflectionExtractor];

        $propertyInfo = new PropertyInfoExtractor(
            $propertyInitializableExtractors,
            $descriptionExtractors,
            $typeExtractors,
        );

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $normalizer = new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter, null, $propertyInfo);
        $normalizers = [new DateTimeNormalizer(), $normalizer, new ArrayDenormalizer()];

        $namespaces = [
            'soapenv' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'sum' => 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroLR.xsd',
            'sum1' => 'https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd',
            'xd' => 'http://www.w3.org/2000/09/xmldsig#'
        ];
        
        $encoders = [
            new XmlEncoder([
                'xml_root_node_name' => 'soapenv:Envelope',
                'xml_format_output' => true,
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                'xml_encoding' => 'UTF-8',
                'xml_version' => '1.0',
                'namespace_prefix_map' => $namespaces,
                'default_root_ns' => 'soapenv',
                'xml_namespaces' => array_combine(
                    array_map(fn($prefix) => "xmlns:$prefix", array_keys($namespaces)),
                    array_values($namespaces)
                )
            ]), 
            new JsonEncoder()
        ];

        return new Serializer($normalizers, $encoders);
    }
    
    /**
     * removeEmptyValues
     *
     * Removes empty values from the array
     * 
     * @param  array $array
     * @return array
     */
    private function removeEmptyValues(array $array): array
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->removeEmptyValues($value);
                if (empty($array[$key])) {
                    unset($array[$key]);
                }
            } elseif ($value === null || $value === '') {
                unset($array[$key]);
            }
        }
        
        return $array;
    }
}

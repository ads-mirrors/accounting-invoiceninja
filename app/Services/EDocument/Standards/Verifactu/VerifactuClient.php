<?php
/**
 * VerifactuClient.php
 *
 * SOAP client for sending invoices (facturas) to AEAT Verifactu service.
 * Supports production and test endpoints via a mode switch.
 */
namespace App\Services\EDocument\Standards\Verifactu;

use App\Services\EDocument\Standards\Verifactu\Types\RegFactuSistemaFacturacion;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFactura;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionAlta;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionAnulacion;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionSubsanacion;
use App\Services\EDocument\Standards\Verifactu\Types\Subsanacion;


class VerifactuClient
{
    const MODE_PROD = 'prod';
    const MODE_TEST = 'test';

    /**
     * @var array<string,string>
     */
    private static array $endpoints = [
        self::MODE_PROD => 'https://www1.agenciatributaria.gob.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP',
        self::MODE_TEST => 'https://prewww1.aeat.es/wlpl/TIKE-CONT/ws/SistemaFacturacion/VerifactuSOAP',
    ];

    private \SoapClient $client;
    private string $mode;

    /**
     * @param string      $mode    One of VerifactuClient::MODE_PROD or MODE_TEST
     * @param string|null $wsdl    Path to the WSDL file; defaults to xsd/SistemaFacturacion.wsdl
     * @param array       $options Additional SoapClient options
     *
     * @throws \InvalidArgumentException
     * @throws \SoapFault
     */
    public function __construct(string $mode = self::MODE_TEST, string $wsdl = null, array $options = [])
    {
        if (!isset(self::$endpoints[$mode])) {
            throw new \InvalidArgumentException("Invalid mode '{$mode}', must be 'prod' or 'test'.");
        }
        $this->mode = $mode;
        $endpoint    = self::$endpoints[$mode];
        $wsdlPath    = $wsdl ?: __DIR__ . '/xsd/SistemaFacturacion.wsdl';

        // Default SOAP client options with classmap for generated Types
        $defaultOpts = [
            'trace'        => true,
            'exceptions'   => true,
            'cache_wsdl'   => WSDL_CACHE_NONE,
            'location'     => $endpoint,
            'soap_version' => SOAP_1_1,
            'classmap'     => [
                'RegFactuSistemaFacturacion'     => RegFactuSistemaFacturacion::class,
                'RegistroFactura'                => RegistroFactura::class,
                'RegistroFacturacionAlta'        => RegistroFacturacionAlta::class,
                'RegistroFacturacionAnulacion'   => RegistroFacturacionAnulacion::class,
                'RegistroFacturacionSubsanacion' => Subsanacion::class,
                // Add additional types here as needed
            ],
        ];

        $opts = array_merge($defaultOpts, $options);

        $this->client = new \SoapClient($wsdlPath, $opts);
    }

    /**
     * Send an invoice registration (alta) request
     *
     * @param RegistroFacturacionAlta $registro
     * @return mixed The SOAP response
     * @throws \SoapFault
     */
    public function sendRegistroAlta(RegistroFacturacionAlta $registro)
    {
        $factura = new RegistroFactura();
        $factura->setRegistroAlta($registro);

        $wrapper = new RegFactuSistemaFacturacion();
        $wrapper->addRegistroFactura($factura);

        return $this->sendRegistroFactura($wrapper);
    }

    /**
     * Send an invoice cancellation (anulación) request
     *
     * @param RegistroFacturacionAnulacion $registro
     * @return mixed The SOAP response
     * @throws \SoapFault
     */
    public function sendRegistroAnulacion(RegistroFacturacionAnulacion $registro)
    {
        $factura = new RegistroFactura();
        $factura->setRegistroAnulacion($registro);

        $wrapper = new RegFactuSistemaFacturacion();
        $wrapper->addRegistroFactura($factura);

        return $this->sendRegistroFactura($wrapper);
    }

    /**
     * Low-level send: SoapClient marshals the object per classmap
     *
     * @param RegFactuSistemaFacturacion $wrapper
     * @return mixed                     The SOAP response
     * @throws \SoapFault
     */
    public function sendRegistroFactura(RegFactuSistemaFacturacion $wrapper)
    {
        return $this->client->__soapCall(
            'RegFactuSistemaFacturacion',
            ['RegFactuSistemaFacturacion' => $wrapper]
        );
    }


    /**
     * Get the last raw request XML
     *
     * @return string
     */
    public function getLastRequest(): string
    {
        return $this->client->__getLastRequest();
    }

    /**
     * Get the last raw response XML
     *
     * @return string
     */
    public function getLastResponse(): string
    {
        return $this->client->__getLastResponse();
    }
}

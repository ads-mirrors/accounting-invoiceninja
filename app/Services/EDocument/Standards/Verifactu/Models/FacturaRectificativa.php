<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

class FacturaRectificativa extends BaseXmlModel
{
    private string $tipoRectificativa;
    private float $baseRectificada;
    private float $cuotaRectificada;
    private ?float $cuotaRecargoRectificado;
    private array $facturasRectificadas;

    public function __construct(
        string $tipoRectificativa,
        float $baseRectificada,
        float $cuotaRectificada,
        ?float $cuotaRecargoRectificado = null
    ) {
        $this->tipoRectificativa = $tipoRectificativa;
        $this->baseRectificada = $baseRectificada;
        $this->cuotaRectificada = $cuotaRectificada;
        $this->cuotaRecargoRectificado = $cuotaRecargoRectificado;
        $this->facturasRectificadas = [];
    }

    public function getTipoRectificativa(): string
    {
        return $this->tipoRectificativa;
    }

    public function getBaseRectificada(): float
    {
        return $this->baseRectificada;
    }

    public function getCuotaRectificada(): float
    {
        return $this->cuotaRectificada;
    }

    public function getCuotaRecargoRectificado(): ?float
    {
        return $this->cuotaRecargoRectificado;
    }

    public function addFacturaRectificada(string $nif, string $numSerie, string $fecha): void
    {
        $this->facturasRectificadas[] = [
            'nif' => $nif,
            'numSerie' => $numSerie,
            'fecha' => $fecha
        ];
    }

    public function getFacturasRectificadas(): array
    {
        return $this->facturasRectificadas;
    }

    /**
     * Set up a rectified invoice with the required information
     * 
     * @param string $nif The NIF of the rectified invoice
     * @param string $numSerie The series number of the rectified invoice
     * @param string $fecha The date of the rectified invoice
     * @return self
     */
    public function setRectifiedInvoice(string $nif, string $numSerie, string $fecha): self
    {
        $this->facturasRectificadas = [];
        $this->addFacturaRectificada($nif, $numSerie, $fecha);
        return $this;
    }

    /**
     * Set up a rectified invoice with the required information using an IDFactura object
     * 
     * @param IDFactura $idFactura The IDFactura object of the rectified invoice
     * @return self
     */
    public function setRectifiedInvoiceFromIDFactura(IDFactura $idFactura): self
    {
        $this->facturasRectificadas = [];
        $this->addFacturaRectificada(
            $idFactura->getIdEmisorFactura(),
            $idFactura->getNumSerieFactura(),
            $idFactura->getFechaExpedicionFactura()
        );
        return $this;
    }

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $idFacturaRectificada = $this->createElement($doc, 'IDFacturaRectificada');
        
        // Add required elements in order with proper namespace
        $idEmisorFactura = $this->createElement($doc, 'IDEmisorFactura', $this->facturasRectificadas[0]['nif']);
        $idFacturaRectificada->appendChild($idEmisorFactura);
        
        $numSerieFactura = $this->createElement($doc, 'NumSerieFactura', $this->facturasRectificadas[0]['numSerie']);
        $idFacturaRectificada->appendChild($numSerieFactura);
        
        $fechaExpedicionFactura = $this->createElement($doc, 'FechaExpedicionFactura', $this->facturasRectificadas[0]['fecha']);
        $idFacturaRectificada->appendChild($fechaExpedicionFactura);

        // Add required fields for R1 invoices according to Verifactu standard
        $baseRectificada = $this->createElement($doc, 'BaseRectificada', number_format($this->baseRectificada, 2, '.', ''));
        $idFacturaRectificada->appendChild($baseRectificada);
        
        $cuotaRectificada = $this->createElement($doc, 'CuotaRectificada', number_format($this->cuotaRectificada, 2, '.', ''));
        $idFacturaRectificada->appendChild($cuotaRectificada);
        
        // Add optional CuotaRecargoRectificado if set
        if ($this->cuotaRecargoRectificado !== null) {
            $cuotaRecargoRectificado = $this->createElement($doc, 'CuotaRecargoRectificado', number_format($this->cuotaRecargoRectificado, 2, '.', ''));
            $idFacturaRectificada->appendChild($cuotaRecargoRectificado);
        }

        return $idFacturaRectificada;
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        // This method is required by BaseXmlModel but not used in this context
        // Return a default instance
        return new self('S', 0.0, 0.0);
    }
} 
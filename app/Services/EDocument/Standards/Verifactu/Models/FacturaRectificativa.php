<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

class FacturaRectificativa
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

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $idFacturaRectificada = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sf:IDFacturaRectificada');
        
        // Add required elements in order with proper namespace
        $idEmisorFactura = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sf:IDEmisorFactura');
        $idEmisorFactura->nodeValue = $this->facturasRectificadas[0]['nif'];
        $idFacturaRectificada->appendChild($idEmisorFactura);
        
        $numSerieFactura = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sf:NumSerieFactura');
        $numSerieFactura->nodeValue = $this->facturasRectificadas[0]['numSerie'];
        $idFacturaRectificada->appendChild($numSerieFactura);
        
        $fechaExpedicionFactura = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sf:FechaExpedicionFactura');
        $fechaExpedicionFactura->nodeValue = $this->facturasRectificadas[0]['fecha'];
        $idFacturaRectificada->appendChild($fechaExpedicionFactura);

        return $idFacturaRectificada;
    }
} 
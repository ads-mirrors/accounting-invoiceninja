<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

class Desglose extends BaseXmlModel
{
    protected ?array $desgloseFactura = null;
    protected ?array $desgloseTipoOperacion = null;
    protected ?array $desgloseIVA = null;
    protected ?array $desgloseIGIC = null;
    protected ?array $desgloseIRPF = null;
    protected ?array $desgloseIS = null;

    public function toXml(): string
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $root = $this->createElement($doc, 'Desglose');
        $doc->appendChild($root);

        // Create DetalleDesglose element
        $detalleDesglose = $this->createElement($doc, 'DetalleDesglose');

        // Handle regular invoice desglose
        if ($this->desgloseFactura !== null) {
            // Add Impuesto if present
            if (isset($this->desgloseFactura['Impuesto'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'Impuesto', $this->desgloseFactura['Impuesto']));
            }

            // Add ClaveRegimen if present
            if (isset($this->desgloseFactura['ClaveRegimen'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'ClaveRegimen', $this->desgloseFactura['ClaveRegimen']));
            }

            // Add either CalificacionOperacion or OperacionExenta
            if (isset($this->desgloseFactura['OperacionExenta'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'OperacionExenta', $this->desgloseFactura['OperacionExenta']));
            } else {
                $detalleDesglose->appendChild($this->createElement($doc, 'CalificacionOperacion', 
                    $this->desgloseFactura['CalificacionOperacion'] ?? 'S1'));
            }

            // Add TipoImpositivo if present
            if (isset($this->desgloseFactura['TipoImpositivo'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'TipoImpositivo', 
                    number_format($this->desgloseFactura['TipoImpositivo'], 2, '.', '')));
            }

            // Add BaseImponibleOimporteNoSujeto (required)
            $detalleDesglose->appendChild($this->createElement($doc, 'BaseImponibleOimporteNoSujeto', 
                number_format($this->desgloseFactura['BaseImponible'], 2, '.', '')));

            // Add BaseImponibleACoste if present
            if (isset($this->desgloseFactura['BaseImponibleACoste'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'BaseImponibleACoste', 
                    number_format($this->desgloseFactura['BaseImponibleACoste'], 2, '.', '')));
            }

            // Add CuotaRepercutida if present
            if (isset($this->desgloseFactura['Cuota'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'CuotaRepercutida', 
                    number_format($this->desgloseFactura['Cuota'], 2, '.', '')));
            }

            // Add TipoRecargoEquivalencia if present
            if (isset($this->desgloseFactura['TipoRecargoEquivalencia'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'TipoRecargoEquivalencia', 
                    number_format($this->desgloseFactura['TipoRecargoEquivalencia'], 2, '.', '')));
            }

            // Add CuotaRecargoEquivalencia if present
            if (isset($this->desgloseFactura['CuotaRecargoEquivalencia'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'CuotaRecargoEquivalencia', 
                    number_format($this->desgloseFactura['CuotaRecargoEquivalencia'], 2, '.', '')));
            }
        }
        
        // Handle simplified invoice desglose (IVA)
        if ($this->desgloseIVA !== null) {
            // Add Impuesto (required for IVA)
            $detalleDesglose->appendChild($this->createElement($doc, 'Impuesto', '01'));

            // Add ClaveRegimen (required for simplified invoices)
            $detalleDesglose->appendChild($this->createElement($doc, 'ClaveRegimen', '02'));

            // Add CalificacionOperacion (required)
            $detalleDesglose->appendChild($this->createElement($doc, 'CalificacionOperacion', 'S2'));

            // Add TipoImpositivo if present
            if (isset($this->desgloseIVA['TipoImpositivo'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'TipoImpositivo', 
                    number_format($this->desgloseIVA['TipoImpositivo'], 2, '.', '')));
            }

            // Add BaseImponibleOimporteNoSujeto (required)
            $detalleDesglose->appendChild($this->createElement($doc, 'BaseImponibleOimporteNoSujeto', 
                number_format($this->desgloseIVA['BaseImponible'], 2, '.', '')));

            // Add CuotaRepercutida if present
            if (isset($this->desgloseIVA['Cuota'])) {
                $detalleDesglose->appendChild($this->createElement($doc, 'CuotaRepercutida', 
                    number_format($this->desgloseIVA['Cuota'], 2, '.', '')));
            }
        }

        // Only add DetalleDesglose if it has child elements
        if ($detalleDesglose->hasChildNodes()) {
            $root->appendChild($detalleDesglose);
        }

        return $doc->saveXML();
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $desglose = new self();
        
        // Parse DesgloseFactura
        $desgloseFacturaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DesgloseFactura')->item(0);
        if ($desgloseFacturaElement) {
            $desgloseFactura = [];
            foreach ($desgloseFacturaElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $desgloseFactura[$child->localName] = $child->nodeValue;
                }
            }
            $desglose->setDesgloseFactura($desgloseFactura);
        }

        // Parse DesgloseTipoOperacion
        $desgloseTipoOperacionElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DesgloseTipoOperacion')->item(0);
        if ($desgloseTipoOperacionElement) {
            $desgloseTipoOperacion = [];
            foreach ($desgloseTipoOperacionElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $desgloseTipoOperacion[$child->localName] = $child->nodeValue;
                }
            }
            $desglose->setDesgloseTipoOperacion($desgloseTipoOperacion);
        }

        // Parse DesgloseIVA
        $desgloseIvaElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DesgloseIVA')->item(0);
        if ($desgloseIvaElement) {
            $desgloseIva = [];
            foreach ($desgloseIvaElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $desgloseIva[$child->localName] = $child->nodeValue;
                }
            }
            $desglose->setDesgloseIVA($desgloseIva);
        }

        // Parse DesgloseIGIC
        $desgloseIgicElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DesgloseIGIC')->item(0);
        if ($desgloseIgicElement) {
            $desgloseIgic = [];
            foreach ($desgloseIgicElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $desgloseIgic[$child->localName] = $child->nodeValue;
                }
            }
            $desglose->setDesgloseIGIC($desgloseIgic);
        }

        // Parse DesgloseIRPF
        $desgloseIrpfElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DesgloseIRPF')->item(0);
        if ($desgloseIrpfElement) {
            $desgloseIrpf = [];
            foreach ($desgloseIrpfElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $desgloseIrpf[$child->localName] = $child->nodeValue;
                }
            }
            $desglose->setDesgloseIRPF($desgloseIrpf);
        }

        // Parse DesgloseIS
        $desgloseIsElement = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'DesgloseIS')->item(0);
        if ($desgloseIsElement) {
            $desgloseIs = [];
            foreach ($desgloseIsElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $desgloseIs[$child->localName] = $child->nodeValue;
                }
            }
            $desglose->setDesgloseIS($desgloseIs);
        }

        return $desglose;
    }

    public function getDesgloseFactura(): ?array
    {
        return $this->desgloseFactura;
    }

    public function setDesgloseFactura(?array $desgloseFactura): self
    {
        $this->desgloseFactura = $desgloseFactura;
        return $this;
    }

    public function getDesgloseTipoOperacion(): ?array
    {
        return $this->desgloseTipoOperacion;
    }

    public function setDesgloseTipoOperacion(?array $desgloseTipoOperacion): self
    {
        $this->desgloseTipoOperacion = $desgloseTipoOperacion;
        return $this;
    }

    public function getDesgloseIVA(): ?array
    {
        return $this->desgloseIVA;
    }

    public function setDesgloseIVA(?array $desgloseIVA): self
    {
        $this->desgloseIVA = $desgloseIVA;
        return $this;
    }

    public function getDesgloseIGIC(): ?array
    {
        return $this->desgloseIGIC;
    }

    public function setDesgloseIGIC(?array $desgloseIGIC): self
    {
        $this->desgloseIGIC = $desgloseIGIC;
        return $this;
    }

    public function getDesgloseIRPF(): ?array
    {
        return $this->desgloseIRPF;
    }

    public function setDesgloseIRPF(?array $desgloseIRPF): self
    {
        $this->desgloseIRPF = $desgloseIRPF;
        return $this;
    }

    public function getDesgloseIS(): ?array
    {
        return $this->desgloseIS;
    }

    public function setDesgloseIS(?array $desgloseIS): self
    {
        $this->desgloseIS = $desgloseIS;
        return $this;
    }
} 
<?php

namespace App\Services\EDocument\Standards\Verifactu\Models;

use App\Services\EDocument\Standards\Verifactu\Models\RegistroAnterior;

class Encadenamiento extends BaseXmlModel
{
    protected ?string $primerRegistro = null;
    protected ?EncadenamientoFacturaAnterior $registroAnterior = null;
    protected ?EncadenamientoFacturaAnterior $registroPosterior = null;

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $root = $this->createElement($doc, 'Encadenamiento');

        if ($this->primerRegistro !== null) {
            $root->appendChild($this->createElement($doc, 'PrimerRegistro', 'S'));
        }

        if ($this->registroAnterior !== null) {
            $root->appendChild($this->registroAnterior->toXml($doc));
        }

        if ($this->registroPosterior !== null) {
            $root->appendChild($this->registroPosterior->toXml($doc));
        }

        return $root;
    }

    public static function fromXml($xml): BaseXmlModel
    {
        $encadenamiento = new self();
        
        if (is_string($xml)) {
            error_log("Loading XML in Encadenamiento::fromXml: " . $xml);
            $dom = new \DOMDocument();
            if (!$dom->loadXML($xml)) {
                error_log("Failed to load XML in Encadenamiento::fromXml");
                throw new \DOMException('Invalid XML');
            }
            $element = $dom->documentElement;
        } else {
            $element = $xml;
        }
        
        try {
            // Handle PrimerRegistro
            $primerRegistro = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'PrimerRegistro')->item(0);
            if ($primerRegistro) {
                $encadenamiento->setPrimerRegistro($primerRegistro->nodeValue);
            }
            
            // Handle RegistroAnterior
            $registroAnterior = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'RegistroAnterior')->item(0);
            if ($registroAnterior) {
                $encadenamiento->setRegistroAnterior(EncadenamientoFacturaAnterior::fromDOMElement($registroAnterior));
            }
            
            return $encadenamiento;
        } catch (\Exception $e) {
            error_log("Error parsing XML in Encadenamiento::fromXml: " . $e->getMessage());
            throw new \InvalidArgumentException('Error parsing XML: ' . $e->getMessage());
        }
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $encadenamiento = new self();
        
        // Handle PrimerRegistro
        $primerRegistro = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'PrimerRegistro')->item(0);
        if ($primerRegistro) {
            $encadenamiento->setPrimerRegistro($primerRegistro->nodeValue);
        }
        
        // Handle RegistroAnterior
        $registroAnterior = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'RegistroAnterior')->item(0);
        if ($registroAnterior) {
            $encadenamiento->setRegistroAnterior(EncadenamientoFacturaAnterior::fromDOMElement($registroAnterior));
        }
        
        return $encadenamiento;
    }

    public function getPrimerRegistro(): ?string
    {
        return $this->primerRegistro;
    }

    public function setPrimerRegistro(?string $primerRegistro): self
    {
        if ($primerRegistro !== null && $primerRegistro !== 'S') {
            throw new \InvalidArgumentException('PrimerRegistro must be "S" or null');
        }
        $this->primerRegistro = $primerRegistro;
        return $this;
    }

    public function getRegistroAnterior(): ?RegistroAnterior
    {
        return $this->registroAnterior;
    }

    public function setRegistroAnterior(?RegistroAnterior $registroAnterior): self
    {
        $this->registroAnterior = $registroAnterior;
        return $this;
    }

    public function getRegistroPosterior(): ?EncadenamientoFacturaAnterior
    {
        return $this->registroPosterior;
    }

    public function setRegistroPosterior(?EncadenamientoFacturaAnterior $registroPosterior): self
    {
        $this->registroPosterior = $registroPosterior;
        return $this;
    }
}

class EncadenamientoFacturaAnterior extends BaseXmlModel
{
    protected string $idEmisorFactura;
    protected string $numSerieFactura;
    protected string $fechaExpedicionFactura;
    protected string $huella;

    public function toXml(\DOMDocument $doc): \DOMElement
    {
        $root = $this->createElement($doc, 'RegistroAnterior');

        $root->appendChild($this->createElement($doc, 'IDEmisorFactura', $this->idEmisorFactura));
        $root->appendChild($this->createElement($doc, 'NumSerieFactura', $this->numSerieFactura));
        $root->appendChild($this->createElement($doc, 'FechaExpedicionFactura', $this->fechaExpedicionFactura));
        $root->appendChild($this->createElement($doc, 'Huella', $this->huella));

        return $root;
    }

    public static function fromDOMElement(\DOMElement $element): self
    {
        $registroAnterior = new self();
        
        // Handle IDEmisorFactura
        $idEmisorFactura = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'IDEmisorFactura')->item(0);
        if ($idEmisorFactura) {
            $registroAnterior->setIdEmisorFactura($idEmisorFactura->nodeValue);
        }
        
        // Handle NumSerieFactura
        $numSerieFactura = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'NumSerieFactura')->item(0);
        if ($numSerieFactura) {
            $registroAnterior->setNumSerieFactura($numSerieFactura->nodeValue);
        }
        
        // Handle FechaExpedicionFactura
        $fechaExpedicionFactura = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'FechaExpedicionFactura')->item(0);
        if ($fechaExpedicionFactura) {
            $registroAnterior->setFechaExpedicionFactura($fechaExpedicionFactura->nodeValue);
        }
        
        // Handle Huella
        $huella = $element->getElementsByTagNameNS(self::XML_NAMESPACE, 'Huella')->item(0);
        if ($huella) {
            $registroAnterior->setHuella($huella->nodeValue);
        }
        
        return $registroAnterior;
    }

    public function getIdEmisorFactura(): string
    {
        return $this->idEmisorFactura;
    }

    public function setIdEmisorFactura(string $idEmisorFactura): self
    {
        $this->idEmisorFactura = $idEmisorFactura;
        return $this;
    }

    public function getNumSerieFactura(): string
    {
        return $this->numSerieFactura;
    }

    public function setNumSerieFactura(string $numSerieFactura): self
    {
        $this->numSerieFactura = $numSerieFactura;
        return $this;
    }

    public function getFechaExpedicionFactura(): string
    {
        return $this->fechaExpedicionFactura;
    }

    public function setFechaExpedicionFactura(string $fechaExpedicionFactura): self
    {
        $this->fechaExpedicionFactura = $fechaExpedicionFactura;
        return $this;
    }

    public function getHuella(): string
    {
        return $this->huella;
    }

    public function setHuella(string $huella): self
    {
        $this->huella = $huella;
        return $this;
    }
} 
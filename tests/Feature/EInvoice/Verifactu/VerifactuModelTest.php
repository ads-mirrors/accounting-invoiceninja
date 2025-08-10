<?php

/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace Tests\Feature\EInvoice\Verifactu;

use Tests\TestCase;
use App\Services\EDocument\Standards\Verifactu\Models\Cupon;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\DetalleDesglose;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;
use App\Services\EDocument\Standards\Validation\VerifactuDocumentValidator;
use App\Services\EDocument\Standards\Verifactu\Models\FacturaRectificativa;
use App\Services\EDocument\Standards\Verifactu\Models\PrimerRegistroCadena;
use App\Services\EDocument\Standards\Verifactu\Models\PersonaFisicaJuridica;

class VerifactuModelTest extends TestCase
{
    public function testCreateAndSerializeCompleteInvoice(): void
    {

        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-001')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setRefExterna('REF-123')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Venta de productos varios')
            ->setCuotaTotal(210.00)
            ->setImporteTotal(1000.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add emitter
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('B12345678')
            ->setRazonSocial('Empresa Ejemplo SL');
        $invoice->setTercero($emisor);

        // Add breakdown
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponibleOimporteNoSujeto' => 1000.00,
            'TipoImpositivo' => 21,
            'CuotaRepercutida' => 210.00
        ]);
        $invoice->setDesglose($desglose);

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        // Add chain
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add coupon
        $cupon = new Cupon();
        $cupon
            ->setIdCupon('CUP-001')
            ->setFechaExpedicionCupon('2023-01-01')
            ->setImporteCupon(50.00)
            ->setDescripcionCupon('Descuento promocional');
        // $invoice->setCupon($cupon);

        $xml = $invoice->toXmlString();
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);




        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getRefExterna(), $deserialized->getRefExterna());
        $this->assertEquals($invoice->getNombreRazonEmisor(), $deserialized->getNombreRazonEmisor());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getDescripcionOperacion(), $deserialized->getDescripcionOperacion());
        $this->assertEquals($invoice->getCuotaTotal(), $deserialized->getCuotaTotal());
        $this->assertEquals($invoice->getImporteTotal(), $deserialized->getImporteTotal());
    }

    public function testCreateAndSerializeSimplifiedInvoice(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-002')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F2')
            ->setFacturaSimplificadaArt7273('S')
            ->setDescripcionOperacion('Venta de productos varios')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add breakdown
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'BaseImponibleOimporteNoSujeto' => 100.00,
            'TipoImpositivo' => 21,
            'CuotaRepercutida' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Add encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        $xml = $invoice->toXmlString();
        
        
$xslt = new VerifactuDocumentValidator($xml);
$xslt->validate();
$errors = $xslt->getVerifactuErrors();

if (count($errors) > 0) {
    nlog($xml);
    nlog($errors);
}

$this->assertCount(0, $errors);

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getNombreRazonEmisor(), $deserialized->getNombreRazonEmisor());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getFacturaSimplificadaArt7273(), $deserialized->getFacturaSimplificadaArt7273());
    }

    public function testCreateAndSerializeRectificationInvoice(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-003')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('R1')
            ->setTipoRectificativa('I')
            ->setDescripcionOperacion('Rectificación de factura anterior')
            ->setCuotaTotal(-21.00)
            ->setImporteTotal(-100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Add desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'BaseImponibleOimporteNoSujeto' => -100.00,
            'TipoImpositivo' => 21,
            'CuotaRepercutida' => -21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add rectified invoice
        $facturaRectificativa = new FacturaRectificativa(
            'I',  // tipoRectificativa
            -100.00,  // baseRectificada
            -21.00  // cuotaRectificada
        );
        $facturaRectificativa->addFacturaRectificada(
            'B12345678',  // nif
            'FAC-2023-001',  // numSerie
            '01-01-2023'  // fecha
        );
        $invoice->setFacturaRectificativa($facturaRectificativa);

        $xml = $invoice->toXmlString();
        

$xslt = new VerifactuDocumentValidator($xml);
$xslt->validate();
$errors = $xslt->getVerifactuErrors();

if (count($errors) > 0) {
    nlog($xml);
    nlog($errors);
}

$this->assertCount(0, $errors);

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getNombreRazonEmisor(), $deserialized->getNombreRazonEmisor());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getTipoRectificativa(), $deserialized->getTipoRectificativa());
    }

    public function testCreateAndSerializeR1InvoiceWithImporteRectificacion(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-004')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('R1')
            ->setTipoRectificativa('I')
            ->setImporteRectificacion(100.00)
            ->setDescripcionOperacion('Rectificación completa de factura anterior')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Add desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'BaseImponibleOimporteNoSujeto' => 100.00,
            'TipoImpositivo' => 21,
            'CuotaRepercutida' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add rectified invoice
        $facturaRectificativa = new FacturaRectificativa(
            'I',  // tipoRectificativa
            100.00,  // baseRectificada
            21.00  // cuotaRectificada
        );
        $facturaRectificativa->addFacturaRectificada(
            'B12345678',  // nif
            'FAC-2023-001',  // numSerie
            '01-01-2023'  // fecha
        );
        $invoice->setFacturaRectificativa($facturaRectificativa);

        $xml = $invoice->toXmlString();
        
        // Debug: Check if the property is set correctly
        $this->assertEquals(100.00, $invoice->getImporteRectificacion());
        $this->assertEquals('R1', $invoice->getTipoFactura());
        
        // Debug: Log the XML to see what's actually generated
        nlog('Generated XML: ' . $xml);
        
        // Verify that ImporteRectificacion is included in the XML
        // Note: The XML includes namespace prefix 'sum1:' and the value is formatted as '100' not '100.00'
        $this->assertStringContainsString('<sum1:ImporteRectificacion>100</sum1:ImporteRectificacion>', $xml);
        
        // Validate against Verifactu schema
        $xslt = new VerifactuDocumentValidator($xml);
        $xslt->validate();
        $errors = $xslt->getVerifactuErrors();

        if (count($errors) > 0) {
            nlog($xml);
            nlog($errors);
        }

        $this->assertCount(0, $errors);

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getTipoRectificativa(), $deserialized->getTipoRectificativa());
        $this->assertEquals($invoice->getImporteRectificacion(), $deserialized->getImporteRectificacion());
    }

    public function testCreateAndSerializeInvoiceWithoutRecipient(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-004')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setFacturaSinIdentifDestinatarioArt61d('S')
            ->setDescripcionOperacion('Venta de productos varios')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add information system
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        // Add desglose with correct key names
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'BaseImponibleOimporteNoSujeto' => 100.00,
            'TipoImpositivo' => 21.00,
            'CuotaRepercutida' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        $xml = $invoice->toXmlString();

$xslt = new VerifactuDocumentValidator($xml);
$xslt->validate();
$errors = $xslt->getVerifactuErrors();

if (count($errors) > 0) {
    nlog($xml);
    nlog($errors);
}

$this->assertCount(0, $errors);

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getNombreRazonEmisor(), $deserialized->getNombreRazonEmisor());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getFacturaSinIdentifDestinatarioArt61d(), $deserialized->getFacturaSinIdentifDestinatarioArt61d());
        $this->assertEquals($invoice->getCuotaTotal(), $deserialized->getCuotaTotal());
        $this->assertEquals($invoice->getImporteTotal(), $deserialized->getImporteTotal());
    }

    public function testInvalidXmlThrowsException(): void
    {
        $this->expectException(\DOMException::class);
        
        $invalidXml = '<?xml version="1.0" encoding="UTF-8"?><unclosed>';
        Invoice::fromXml($invalidXml);
    }

    public function testMissingRequiredFieldsThrowsException(): void
    {
        $invoice = new Invoice();
        
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: IDVersion');
        
        $invoice->toXmlString();
    }

    public function test_create_and_serialize_rectification_invoice()
    {
        $invoice = new Invoice();
        $invoice->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-001')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setRefExterna('REF-123')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('R1')
            ->setTipoRectificativa('S')
            ->setDescripcionOperacion('Rectificación de factura')
            ->setTercero((new PersonaFisicaJuridica())
                ->setNif('B12345678')
                ->setRazonSocial('Empresa Ejemplo SL'))
            ->setDesglose((new Desglose())
                ->setDesgloseIVA([
                    'Impuesto' => '01',
                    'ClaveRegimen' => '01',
                    'CalificacionOperacion' => 'S1',
                    'BaseImponibleOimporteNoSujeto' => 1000.00,
                    'TipoImpositivo' => 21.00,
                    'CuotaRepercutida' => 210.00
                ]))
            ->setCuotaTotal(210)
            ->setImporteTotal(1000)
            ->setSistemaInformatico((new SistemaInformatico())
                ->setNombreRazon('Sistema de Facturación')
                ->setNif('B12345678')
                ->setNombreSistemaInformatico('SistemaFacturacion')
                ->setIdSistemaInformatico('01')
                ->setVersion('1.0')
                ->setNumeroInstalacion('INST-001'))
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Create Encadenamiento with PrimerRegistroCadena
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);
        
        $facturaRectificativa = new FacturaRectificativa(
            'S', // TipoRectificativa (S for substitutive)
            1000.00, // BaseRectificada
            210.00, // CuotaRectificada
            null // CuotaRecargoRectificado (optional)
        );
        
        // Add a rectified invoice
        $facturaRectificativa->addFacturaRectificada(
            'B12345678', // NIF
            'FAC-2023-001', // NumSerieFactura
            '24-04-2025' // FechaExpedicionFactura
        );
        
        $invoice->setFacturaRectificativa($facturaRectificativa);

        $xml = $invoice->toXmlString();

$xslt = new VerifactuDocumentValidator($xml);
$xslt->validate();
$errors = $xslt->getVerifactuErrors();

if (count($errors) > 0) {
    nlog($xml);
    nlog($errors);
}

$this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getTipoRectificativa(), $deserialized->getTipoRectificativa());
        $this->assertEquals($invoice->getCuotaTotal(), $deserialized->getCuotaTotal());
        $this->assertEquals($invoice->getImporteTotal(), $deserialized->getImporteTotal());
    }

    public function testCreateAndSerializeInvoiceWithMultipleRecipients(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-005')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Venta a múltiples destinatarios')
            ->setCuotaTotal(42.00)
            ->setImporteTotal(200.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add multiple recipients
        $destinatarios = [];
        $destinatario1 = new PersonaFisicaJuridica();
        $destinatario1
            ->setNif('B87654321')
            ->setNombreRazon('Cliente 1 SL');
        $destinatarios[] = $destinatario1;

        $destinatario2 = new PersonaFisicaJuridica();
        $destinatario2
            ->setPais('FR')
            ->setTipoIdentificacion('02')
            ->setIdOtro('FR12345678901')
            ->setNombreRazon('Client 2 SARL');
        $destinatarios[] = $destinatario2;

        $invoice->setDestinatarios($destinatarios);

        // Add desglose with proper structure and correct key names
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponibleOimporteNoSujeto' => 200.00,
            'TipoImpositivo' => 21.00,
            'CuotaRepercutida' => 42.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento (required)
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
$xslt = new VerifactuDocumentValidator($xml);
$xslt->validate();
$errors = $xslt->getVerifactuErrors();

if (count($errors) > 0) {
    nlog($xml);
    nlog($errors);
}

$this->assertCount(0, $errors);

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals(2, count($deserialized->getDestinatarios()));
        
        // Verify first recipient (with NIF)
        $this->assertEquals('Cliente 1 SL', $deserialized->getDestinatarios()[0]->getNombreRazon());
        $this->assertEquals('B87654321', $deserialized->getDestinatarios()[0]->getNif());
        
        // Verify second recipient (with IDOtro)
        $this->assertEquals('Client 2 SARL', $deserialized->getDestinatarios()[1]->getNombreRazon());
        $this->assertEquals('FR', $deserialized->getDestinatarios()[1]->getPais());
        $this->assertEquals('02', $deserialized->getDestinatarios()[1]->getTipoIdentificacion());
        $this->assertEquals('FR12345678901', $deserialized->getDestinatarios()[1]->getIdOtro());
        $this->assertNull($deserialized->getDestinatarios()[1]->getNif());
    }

    public function testCreateAndSerializeInvoiceWithExemptOperation(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-006')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Operación exenta de IVA')
            ->setCuotaTotal(0.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add desglose with exempt operation
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'N1',
            'BaseImponibleOimporteNoSujeto' => 100.00,
            'TipoImpositivo' => 0,
            'CuotaRepercutida' => 0.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento (required)
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        

      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);



        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals(0.00, $deserialized->getCuotaTotal());
        $this->assertEquals(100.00, $deserialized->getImporteTotal());
    }

    public function testCreateAndSerializeInvoiceWithDifferentTaxRates(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-007')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Venta con diferentes tipos impositivos')
            ->setCuotaTotal(31.50)
            ->setImporteTotal(250.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add desglose with multiple tax rates
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            [
                'Impuesto' => '01',
                'ClaveRegimen' => '01',
                'CalificacionOperacion' => 'S1',
                'BaseImponibleOimporteNoSujeto' => 100.00,
                'TipoImpositivo' => 21.00,
                'CuotaRepercutida' => 21.00
            ],
            [
                'Impuesto' => '01',
                'ClaveRegimen' => '01',
                'CalificacionOperacion' => 'S1',
                'BaseImponibleOimporteNoSujeto' => 150.00,
                'TipoImpositivo' => 7.00,
                'CuotaRepercutida' => 10.50
            ]
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento (required)
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals(31.50, $deserialized->getCuotaTotal());
        $this->assertEquals(250.00, $deserialized->getImporteTotal());
    }

    public function testCreateAndSerializeInvoiceWithSubsequentChain(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-008')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con encadenamiento posterior')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add desglose with proper structure
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento with subsequent chain
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add sistema informatico with all required fields
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('S', $deserialized->getEncadenamiento()->getPrimerRegistro());
    }

    public function testCreateAndSerializeInvoiceWithThirdPartyIssuer(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-009')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura emitida por tercero')
            ->setEmitidaPorTerceroODestinatario('T')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Set up the third party issuer
        $tercero = new PersonaFisicaJuridica();
        $tercero
            ->setNif('B98765432')
            ->setRazonSocial('Tercero Emisor SL');
        $invoice->setTercero($tercero);

        // Add desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento (required)
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add sistema informatico with all required fields
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('T', $deserialized->getEmitidaPorTerceroODestinatario());
        $this->assertEquals('B98765432', $deserialized->getTercero()->getNif());
        $this->assertEquals('Tercero Emisor SL', $deserialized->getTercero()->getRazonSocial());
    }

    public function testCreateAndSerializeInvoiceWithMacroData(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-010')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setMacrodato('S')
            ->setDescripcionOperacion('Factura con macrodato')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        // Add Desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21.00,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add Encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('S', $deserialized->getMacrodato());
    }

    public function testCreateAndSerializeInvoiceWithAgreementData(): void
    {
        $invoice = new Invoice();
        $invoice->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-012')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con datos de acuerdo')
            ->setCuotaTotal(21)
            ->setImporteTotal(100)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...')
            ->setNumRegistroAcuerdoFacturacion('REG-001')
            ->setIdAcuerdoSistemaInformatico('AGR-001');

        // Set up Desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21.00,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Set up Encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Set up SistemaInformatico
        $sistema = new SistemaInformatico();
        $sistema->setNombreRazon('Sistema de Facturación')
            ->setNIF('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistema);

        $xml = $invoice->toXmlString();
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Deserialize and verify
        $deserializedInvoice = Invoice::fromXml($xml);
        $this->assertEquals('REG-001', $deserializedInvoice->getNumRegistroAcuerdoFacturacion());
        $this->assertEquals('AGR-001', $deserializedInvoice->getIdAcuerdoSistemaInformatico());
    }

    public function testCreateAndSerializeInvoiceWithRejectionAndCorrection()
    {
        $invoice = new Invoice();
        $invoice->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-013')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setSubsanacion('S')
            ->setRechazoPrevio('S')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con rechazo y subsanación')
            ->setCuotaTotal(21)
            ->setImporteTotal(100)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add proper Desglose
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'TipoImpositivo' => 21.00,
            'BaseImponible' => 100.00,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add proper Encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Add SistemaInformatico
        $sistemaInformatico = new SistemaInformatico();
        $sistemaInformatico->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistemaInformatico);

        $xml = $invoice->toXmlString();
        $this->assertNotEmpty($xml);
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserializedInvoice = Invoice::fromXml($xml);
        $this->assertEquals('S', $deserializedInvoice->getSubsanacion());
        $this->assertEquals('S', $deserializedInvoice->getRechazoPrevio());
    }

    public function testCreateAndSerializeInvoiceWithOperationDate(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-014')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con fecha de operación')
            ->setFechaOperacion('2023-01-01')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        // Add Desglose
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '02',
            'CalificacionOperacion' => 'S2',
            'TipoImpositivo' => '21.00',
            'BaseImponibleOimporteNoSujeto' => '100.00',
            'CuotaRepercutida' => '21.00'
        ]);
        $invoice->setDesglose($desglose);

        // Add Encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('01-01-2023', $deserialized->getFechaOperacion());
    }

    public function testCreateAndSerializeInvoiceWithCoupon(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-015')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con cupón')
            ->setCupon('S')  // Set cupon to 'S' to indicate it has a coupon
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        // Add Desglose
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'TipoImpositivo' => '21.00',
            'BaseImponibleOimporteNoSujeto' => '100.00',
            'CuotaRepercutida' => '21.00'
        ]);
        $invoice->setDesglose($desglose);

        // Add Encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Generate XML string
        $xml = $invoice->toXmlString();
        
        // Debug output
        // echo "\nGenerated XML:\n";
        // echo $xml;
        // echo "\n\n";
        
        
      $xslt = new VerifactuDocumentValidator($xml);
      $xslt->validate();
      $errors = $xslt->getVerifactuErrors();
      
      if(count($errors) > 0) {
        nlog($xml);
        nlog($errors);
      }

      $this->assertCount(0, $errors);


        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertNotNull($deserialized->getCupon());
        $this->assertEquals('S', $deserialized->getCupon());
    }

    public function testInvalidTipoFacturaThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid TipoFactura value');
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-016')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('INVALID'); // This should throw the exception immediately
    }

    public function testInvalidTipoRectificativaThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-017')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('R1')
            ->setTipoRectificativa('INVALID') // Invalid type
            ->setDescripcionOperacion('Rectificación inválida')
            ->setCuotaTotal(-21.00)
            ->setImporteTotal(-100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        $invoice->toXmlString();
    }


    public function testInvalidNIFFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-019')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con NIF inválido')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add emitter with invalid NIF
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('INVALID_NIF')
            ->setRazonSocial('Empresa Ejemplo SL');
        $invoice->setTercero($emisor);

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        $invoice->toXmlString();
    }

    public function testInvalidAmountFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('FAC-2023-020')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con importe inválido')
            ->setCuotaTotal(21.00)
            ->setImporteTotal('INVALID') // Invalid format
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('SistemaFacturacion')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('INST-001');
        $invoice->setSistemaInformatico($sistema);

        $invoice->toXmlString();
    }

    public function testInvalidSchemaThrowsException(): void
    {
        $this->expectException(\DOMException::class);
        
        $invoice = new Invoice();
        $invoice->setIdVersion('1.0')
            ->setIdFactura((new \App\Services\EDocument\Standards\Verifactu\Models\IDFactura())
                ->setIdEmisorFactura('B12345678')
                ->setNumSerieFactura('TEST123')
                ->setFechaExpedicionFactura('01-01-2023'))
            ->setNombreRazonEmisor('Test Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Test Operation')
            ->setCuotaTotal(100.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro(date('Y-m-d\TH:i:s'))
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add required sistema informatico with valid values
        $sistema = new SistemaInformatico();
        $sistema->setNombreRazon('Test System')
            ->setNif('B12345678')
            ->setNombreSistemaInformatico('Test Software')
            ->setIdSistemaInformatico('01')
            ->setVersion('1.0')
            ->setNumeroInstalacion('001');
        $invoice->setSistemaInformatico($sistema);

        // Add required desglose with DetalleDesglose
        $desglose = new Desglose();
        $detalleDesglose = new DetalleDesglose();
        $detalleDesglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21.00,
            'Cuota' => 21.00
        ]);
        $desglose->setDetalleDesglose($detalleDesglose);
        $invoice->setDesglose($desglose);

        // Add required encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        // Generate valid XML first
        $validXml = $invoice->toXmlString();

        // Create a new document with the valid XML
        $doc = new \DOMDocument();
        $doc->loadXML($validXml);

        // Add an invalid element to trigger schema validation error
        $invalidElement = $doc->createElementNS('https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd', 'sum1:InvalidElement');
        $invalidElement->textContent = 'test';
        $doc->documentElement->appendChild($invalidElement);
        
        // Try to validate the invalid XML using our validateXml method
        $reflectionClass = new \ReflectionClass(Invoice::class);
        $validateXmlMethod = $reflectionClass->getMethod('validateXml');
        $validateXmlMethod->setAccessible(true);
        $validateXmlMethod->invoke(new Invoice(), $doc);

        $xslt = new XsltDocumentValidator($validXml);
        $xslt->validate();

        $this->assertCount(0, $xslt->getErrors());
    }

    protected function assertXmlEquals(string $expectedXml, string $actualXml): void
    {
        $this->assertEquals(
            $this->normalizeXml($expectedXml),
            $this->normalizeXml($actualXml)
        );
    }

    protected function normalizeXml(string $xml): string
    {
        $doc = new \DOMDocument('1.0');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        if (!$doc->loadXML($xml)) {
            throw new \DOMException('Failed to load XML in normalizeXml');
        }
        return $doc->saveXML();
    }

    protected function assertValidatesAgainstXsd(string $xml, string $xsdPath): void
    {
        try {
            $doc = new \DOMDocument();
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;
            if (!$doc->loadXML($xml, LIBXML_NOBLANKS)) {
                throw new \DOMException('Failed to load XML in assertValidatesAgainstXsd');
            }
            
            libxml_use_internal_errors(true);
            $result = $doc->schemaValidate($xsdPath);
            if (!$result) {
                foreach (libxml_get_errors() as $error) {
                }
                libxml_clear_errors();
            }
            
            $this->assertTrue(
                $result,
                'XML does not validate against XSD schema'
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function getTestXsdPath(): string
    {
        return __DIR__ . '/../schema/SuministroInformacion.xsd';
    }
} 
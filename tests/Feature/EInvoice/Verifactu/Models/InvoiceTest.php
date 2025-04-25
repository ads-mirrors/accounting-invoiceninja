<?php

namespace Tests\Feature\EInvoice\Verifactu\Models;

use Tests\Feature\EInvoice\Verifactu\Models\BaseModelTest;
use App\Services\EDocument\Standards\Verifactu\Models\Cupon;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;
use App\Services\EDocument\Standards\Verifactu\Models\PrimerRegistroCadena;
use App\Services\EDocument\Standards\Verifactu\Models\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Models\FacturaRectificativa;

class InvoiceTest extends BaseModelTest
{
    public function testCreateAndSerializeCompleteInvoice(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-001')
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
            'BaseImponible' => 1000.00,
            'TipoImpositivo' => 21,
            'Cuota' => 210.00
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

        $xml = $invoice->toXml();
        
        // Debug output
        echo "\nGenerated XML:\n";
        echo $xml;
        echo "\n\n";

        // Validate against XSD
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        
        if (!$doc->schemaValidate($this->getTestXsdPath())) {
            echo "\nValidation Errors:\n";
            libxml_use_internal_errors(true);
            $doc->schemaValidate($this->getTestXsdPath());
            foreach (libxml_get_errors() as $error) {
                echo $error->message . "\n";
            }
            libxml_clear_errors();
        }

        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

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
            ->setIdFactura('FAC-2023-002')
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
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21,
            'Cuota' => 21.00
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

        $xml = $invoice->toXml();
        
        // Debug output
        echo "\nGenerated XML:\n";
        echo $xml;
        echo "\n\n";

        // Validate against XSD
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

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
            ->setIdFactura('FAC-2023-003')
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
            'BaseImponible' => -100.00,
            'TipoImpositivo' => 21,
            'Cuota' => -21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add FacturaRectificativa
        $facturaRectificativa = new FacturaRectificativa(
            'I',  // TipoRectificativa
            -100.00,  // BaseRectificada
            -21.00  // CuotaRectificada
        );
        $facturaRectificativa->addFacturaRectificada(
            'B12345678',  // NIF
            'FAC-2023-001',  // NumSerieFactura
            '01-01-2023'  // FechaExpedicionFactura
        );
        $invoice->setFacturaRectificativa($facturaRectificativa);

        // Add encadenamiento with PrimerRegistro
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        $xml = $invoice->toXml();
        
        // Debug output
        echo "\nGenerated XML:\n";
        echo $xml;
        echo "\n\n";

        // Validate against XSD
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getNombreRazonEmisor(), $deserialized->getNombreRazonEmisor());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getTipoRectificativa(), $deserialized->getTipoRectificativa());
    }

    public function testCreateAndSerializeInvoiceWithoutRecipient(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-004')
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

        // Add desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Add encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $invoice->setEncadenamiento($encadenamiento);

        $xml = $invoice->toXml();
        
        // Validate against XSD
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals($invoice->getIdVersion(), $deserialized->getIdVersion());
        $this->assertEquals($invoice->getIdFactura(), $deserialized->getIdFactura());
        $this->assertEquals($invoice->getNombreRazonEmisor(), $deserialized->getNombreRazonEmisor());
        $this->assertEquals($invoice->getTipoFactura(), $deserialized->getTipoFactura());
        $this->assertEquals($invoice->getFacturaSinIdentifDestinatarioArt61d(), $deserialized->getFacturaSinIdentifDestinatarioArt61d());
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
        
        $invoice->toXml();
    }

    public function test_create_and_serialize_rectification_invoice()
    {
        $invoice = new Invoice();
        $invoice->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-001')
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
                    'BaseImponible' => 1000.00,
                    'TipoImpositivo' => 21,
                    'Cuota' => 210.00
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

        $xml = $invoice->toXml();
        
        // Debug output
        echo "\nGenerated XML:\n";
        echo $xml;
        echo "\n\n";

        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());
    }

    public function testCreateAndSerializeInvoiceWithMultipleRecipients(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-005')
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
            ->setNombreRazon('Cliente 1 SL');  // Changed from setRazonSocial to setNombreRazon
        $destinatarios[] = $destinatario1;

        $destinatario2 = new PersonaFisicaJuridica();
        $destinatario2
            ->setPais('FR')  // French company
            ->setTipoIdentificacion('02')  // NIF-IVA (VAT number)
            ->setIdOtro('FR12345678901')  // French VAT number
            ->setNombreRazon('Client 2 SARL');  // French company name
        $destinatarios[] = $destinatario2;

        $invoice->setDestinatarios($destinatarios);

        // Add desglose with proper structure
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponible' => 200.00,
            'TipoImpositivo' => 21.00,
            'Cuota' => 42.00
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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

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
            ->setIdFactura('FAC-2023-006')
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
            'OperacionExenta' => 'E1',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 0,
            'Cuota' => 0.00
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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

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
            ->setIdFactura('FAC-2023-007')
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
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21,
            'Cuota' => 21.00
        ]);
        $desglose->setDesgloseIVA([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'BaseImponible' => 150.00,
            'TipoImpositivo' => 7,
            'Cuota' => 10.50
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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

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
            ->setIdFactura('FAC-2023-008')
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

        $xml = $invoice->toXml();
        
        // Debug output
        echo "\nGenerated XML:\n";
        echo $xml;
        echo "\n\n";
        
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('S', $deserialized->getEncadenamiento()->getPrimerRegistro());
    }

    public function testCreateAndSerializeInvoiceWithThirdPartyIssuer(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-009')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setEmitidaPorTerceroODestinatario('T')
            ->setDescripcionOperacion('Factura emitida por tercero')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add desglose with proper structure
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

        // Add third party with proper structure
        $tercero = new PersonaFisicaJuridica();
        $tercero
            ->setRazonSocial('Tercero Emisor SL')
            ->setNif('B98765432');
        $invoice->setTercero($tercero);

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

        $xml = $invoice->toXml();
        
        // Debug output
        echo "\nGenerated XML:\n";
        echo $xml;
        echo "\n\n";
        
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

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
            ->setIdFactura('FAC-2023-010')
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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('S', $deserialized->getMacrodato());
    }

    public function testCreateAndSerializeInvoiceWithDigitalSignature(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-011')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con firma digital')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

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

        // Add desglose with proper structure
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

        // Set up paths for certificates
        $certsPath = dirname(__DIR__) . '/certs/';
        $privateKeyPath = $certsPath . 'private.pem';
        $publicKeyPath = $certsPath . 'public.pem';
        $certificatePath = $certsPath . 'certificate.pem';

        // Check if certificate files exist and are readable
        foreach (['private.pem', 'public.pem', 'certificate.pem'] as $file) {
            $path = $certsPath . $file;
            echo "\n$file: ";
            if (!file_exists($path)) {
                echo "MISSING";
                throw new \RuntimeException("Certificate file $file does not exist at $path");
            }
            if (!is_readable($path)) {
                echo "NOT READABLE";
                throw new \RuntimeException("Certificate file $file is not readable at $path");
            }
            echo "OK (size: " . filesize($path) . " bytes)";
        }

        // Set the keys
        $invoice->setPrivateKeyPath($privateKeyPath)
                ->setPublicKeyPath($publicKeyPath)
                ->setCertificatePath($certificatePath);

        // Generate signed XML
        $xml = $invoice->toXml();

        // Debug output
        echo "\nGenerated XML with signature:\n";
        echo $xml;
        echo "\n\n";

        // Load the XML into a DOMDocument for verification
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        // Verify the signature
        $this->assertTrue($invoice->verifySignature($doc));

        // Skip schema validation for signed XML since the signature schema is not part of the core invoice schema
        // Instead, validate the XML before signing
        $unsignedXml = $invoice->toXml(false);
        $this->assertValidatesAgainstXsd($unsignedXml, $this->getTestXsdPath());
    }

    public function testCreateAndSerializeInvoiceWithAgreementData(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-012')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con datos de acuerdo')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...')
            ->setNumRegistroAcuerdoFacturacion('REG-001')
            ->setIdAcuerdoSistemaInformatico('AGR-001');

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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('REG-001', $deserialized->getNumRegistroAcuerdoFacturacion());
        $this->assertEquals('AGR-001', $deserialized->getIdAcuerdoSistemaInformatico());
    }

    public function testCreateAndSerializeInvoiceWithRejectionAndCorrection(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-013')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setRechazoPrevio('S')
            ->setSubsanacion('S')
            ->setDescripcionOperacion('Factura con rechazo y subsanación')
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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('S', $deserialized->getRechazoPrevio());
        $this->assertEquals('S', $deserialized->getSubsanacion());
    }

    public function testCreateAndSerializeInvoiceWithOperationDate(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-014')
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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertEquals('2023-01-01', $deserialized->getFechaOperacion());
    }

    public function testCreateAndSerializeInvoiceWithCoupon(): void
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-015')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con cupón')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('abc123...');

        // Add coupon
        $cupon = new Cupon();
        $cupon
            ->setIdCupon('CUP-001')
            ->setFechaExpedicionCupon('2023-01-01')
            ->setImporteCupon(10.00)
            ->setDescripcionCupon('Descuento promocional');
        // $invoice->setCupon($cupon);

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

        $xml = $invoice->toXml();
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Test deserialization
        $deserialized = Invoice::fromXml($xml);
        $this->assertNotNull($deserialized->getCupon());
        $this->assertEquals('CUP-001', $deserialized->getCupon()->getIdCupon());
    }

    public function testInvalidTipoFacturaThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-016')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('INVALID') // Invalid type
            ->setDescripcionOperacion('Factura inválida')
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

        $invoice->toXml();
    }

    public function testInvalidTipoRectificativaThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-017')
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

        $invoice->toXml();
    }

    public function testInvalidDateFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-018')
            ->setNombreRazonEmisor('Empresa Ejemplo SL')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Factura con fecha inválida')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(100.00)
            ->setFechaHoraHusoGenRegistro('2023-01-01') // Invalid format
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

        $invoice->toXml();
    }

    public function testInvalidNIFFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-019')
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

        $invoice->toXml();
    }

    public function testInvalidAmountFormatThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('FAC-2023-020')
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

        $invoice->toXml();
    }

    public function testInvalidSchemaThrowsException(): void
    {
        $this->expectException(\DOMException::class);
        
        $invalidXml = '<?xml version="1.0" encoding="UTF-8"?><sf:RegistroAlta xmlns:sf="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/tike/cont/ws/SuministroInformacion.xsd"><sf:InvalidElement>test</sf:InvalidElement></sf:RegistroAlta>';
        
        $doc = new \DOMDocument();
        $doc->loadXML($invalidXml);
        
        if (!$doc->schemaValidate($this->getTestXsdPath())) {
            throw new \DOMException('XML does not validate against schema');
        }
    }

    public function testSignatureGeneration(): void
    {
        $invoice = new Invoice();
        $invoice->setIdVersion('1.0')
            ->setIdFactura('TEST123')
            ->setNombreRazonEmisor('Test Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Test Operation')
            ->setCuotaTotal(100.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro(date('Y-m-d\TH:i:s'))
            ->setTipoHuella('SHA-256')
            ->setHuella(hash('sha256', 'test'));

        // Set up the desglose
        $desglose = new Desglose();
        $desglose->setDesgloseIVA([
            'Impuesto' => 'IVA',
            'ClaveRegimen' => '01',
            'BaseImponible' => 100.00,
            'TipoImpositivo' => 21.00,
            'Cuota' => 21.00
        ]);
        $invoice->setDesglose($desglose);

        // Set up encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('1');
        $invoice->setEncadenamiento($encadenamiento);

        // Set up sistema informatico
        $sistemaInformatico = new SistemaInformatico();
        $sistemaInformatico->setNombreRazon('Test System')
            ->setNif('12345678Z')
            ->setNombreSistemaInformatico('Test Software')
            ->setIdSistemaInformatico('TEST001')
            ->setVersion('1.0')
            ->setNumeroInstalacion('001')
            ->setTipoUsoPosibleSoloVerifactu('S')
            ->setTipoUsoPosibleMultiOT('S')
            ->setIndicadorMultiplesOT('S');
        $invoice->setSistemaInformatico($sistemaInformatico);

        // Set up signature keys
        $privateKeyPath = dirname(__DIR__) . '/certs/private.pem';
        $publicKeyPath = dirname(__DIR__) . '/certs/public.pem';
        $certificatePath = dirname(__DIR__) . '/certs/certificate.pem';

        // Set the keys
        $invoice->setPrivateKeyPath($privateKeyPath)
                ->setPublicKeyPath($publicKeyPath)
                ->setCertificatePath($certificatePath);

        // Generate signed XML
        $xml = $invoice->toXml();

        // Debug output
        echo "\nGenerated XML with signature:\n";
        echo $xml;
        echo "\n\n";

        // Load the XML into a DOMDocument for verification
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        // Verify the signature
        $this->assertTrue($invoice->verifySignature($doc));

        // Validate against schema
        $this->assertValidatesAgainstXsd($xml, $this->getTestXsdPath());

        // Clean up test keys
    }
} 
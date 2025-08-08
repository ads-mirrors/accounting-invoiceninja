<?php

namespace Tests\Feature\EInvoice\Verifactu\Models;

use Tests\TestCase;
use App\Services\EDocument\Standards\Verifactu\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Models\InvoiceModification;
use App\Services\EDocument\Standards\Verifactu\Models\RegistroAnulacion;
use App\Services\EDocument\Standards\Verifactu\Models\RegistroModificacion;
use App\Services\EDocument\Standards\Verifactu\Models\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Models\Desglose;
use App\Services\EDocument\Standards\Verifactu\Models\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Models\SistemaInformatico;

class InvoiceModificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_registro_anulacion()
    {
        $cancellation = new RegistroAnulacion();
        $cancellation
            ->setIdEmisorFactura('99999910G')
            ->setNumSerieFactura('TEST0033343436')
            ->setFechaExpedicionFactura('02-07-2025')
            ->setMotivoAnulacion('1');

        $this->assertEquals('99999910G', $cancellation->getIdEmisorFactura());
        $this->assertEquals('TEST0033343436', $cancellation->getNumSerieFactura());
        $this->assertEquals('02-07-2025', $cancellation->getFechaExpedicionFactura());
        $this->assertEquals('1', $cancellation->getMotivoAnulacion());

        $xml = $cancellation->toXmlString();
        $this->assertStringContainsString('RegistroAnulacion', $xml);
        $this->assertStringContainsString('99999910G', $xml);
        $this->assertStringContainsString('TEST0033343436', $xml);
        $this->assertStringContainsString('02-07-2025', $xml);
        $this->assertStringContainsString('1', $xml);
    }

    public function test_can_create_registro_modificacion()
    {
        $modification = new RegistroModificacion();
        $modification
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Test invoice modification')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-02T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('TEST_HASH');

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $modification->setSistemaInformatico($sistema);

        // Add desglose
        $desglose = new Desglose();
        $desglose->setDesgloseFactura([
            'Impuesto' => '01',
            'ClaveRegimen' => '01',
            'CalificacionOperacion' => 'S1',
            'TipoImpositivo' => '21',
            'BaseImponibleOimporteNoSujeto' => '100.00',
            'CuotaRepercutida' => '21.00'
        ]);
        $modification->setDesglose($desglose);

        // Add encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setPrimerRegistro('S');
        $modification->setEncadenamiento($encadenamiento);

        $this->assertEquals('1.0', $modification->getIdVersion());
        $this->assertEquals('TEST0033343436', $modification->getIdFactura());
        $this->assertEquals('CERTIFICADO FISICA PRUEBAS', $modification->getNombreRazonEmisor());
        $this->assertEquals('F1', $modification->getTipoFactura());
        $this->assertEquals(21.00, $modification->getCuotaTotal());
        $this->assertEquals(121.00, $modification->getImporteTotal());

        $xml = $modification->toXmlString();
        
        $this->assertStringContainsString('RegistroModificacion', $xml);
        $this->assertStringContainsString('TEST0033343436', $xml);
        $this->assertStringContainsString('CERTIFICADO FISICA PRUEBAS', $xml);
        $this->assertStringContainsString('21', $xml);
        $this->assertStringContainsString('121', $xml);
    }

    public function test_can_create_invoice_modification_from_invoices()
    {
        // Create original invoice
        $originalInvoice = new Invoice();
        $originalInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Original Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Original invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('ORIGINAL_HASH');

        // Add emitter to original invoice
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('99999910G')
            ->setRazonSocial('Original Company');
        $originalInvoice->setTercero($emisor);

        // Add sistema informatico to original invoice
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $originalInvoice->setSistemaInformatico($sistema);

        // Create modified invoice
        $modifiedInvoice = new Invoice();
        $modifiedInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Modified Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Modified invoice')
            ->setCuotaTotal(42.00)
            ->setImporteTotal(242.00)
            ->setFechaHoraHusoGenRegistro('2025-01-02T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('MODIFIED_HASH');

        // Add emitter to modified invoice
        $emisorModificado = new PersonaFisicaJuridica();
        $emisorModificado
            ->setNif('99999910G')
            ->setRazonSocial('Modified Company');
        $modifiedInvoice->setTercero($emisorModificado);

        // Add sistema informatico to modified invoice
        $modifiedInvoice->setSistemaInformatico($sistema);

        // Create modification
        $modification = InvoiceModification::createFromInvoice($originalInvoice, $modifiedInvoice);

        $this->assertInstanceOf(InvoiceModification::class, $modification);
        $this->assertInstanceOf(RegistroAnulacion::class, $modification->getRegistroAnulacion());
        $this->assertInstanceOf(RegistroModificacion::class, $modification->getRegistroModificacion());

        // Test cancellation record
        $cancellation = $modification->getRegistroAnulacion();
        $this->assertEquals('99999910G', $cancellation->getIdEmisorFactura());
        $this->assertEquals('TEST0033343436', $cancellation->getNumSerieFactura());
        $this->assertEquals('1', $cancellation->getMotivoAnulacion());

        // Test modification record
        $modificationRecord = $modification->getRegistroModificacion();
        $this->assertEquals('Modified Company', $modificationRecord->getNombreRazonEmisor());
        $this->assertEquals(42.00, $modificationRecord->getCuotaTotal());
        $this->assertEquals(242.00, $modificationRecord->getImporteTotal());
    }

    public function test_can_generate_modification_soap_envelope()
    {
        // Create original invoice
        $originalInvoice = new Invoice();
        $originalInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Original Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Original invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('ORIGINAL_HASH');

        // Add emitter to original invoice
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('99999910G')
            ->setRazonSocial('Original Company');
        $originalInvoice->setTercero($emisor);

        // Add sistema informatico to original invoice
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $originalInvoice->setSistemaInformatico($sistema);

        // Create modified invoice
        $modifiedInvoice = new Invoice();
        $modifiedInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Modified Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Modified invoice')
            ->setCuotaTotal(42.00)
            ->setImporteTotal(242.00)
            ->setFechaHoraHusoGenRegistro('2025-01-02T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('MODIFIED_HASH');

        // Add emitter to modified invoice
        $emisorModificado = new PersonaFisicaJuridica();
        $emisorModificado
            ->setNif('99999910G')
            ->setRazonSocial('Modified Company');
        $modifiedInvoice->setTercero($emisorModificado);

        // Add sistema informatico to modified invoice
        $modifiedInvoice->setSistemaInformatico($sistema);

        // Create modification
        $modification = InvoiceModification::createFromInvoice($originalInvoice, $modifiedInvoice);

        // Generate SOAP envelope
        $soapXml = $modification->toSoapEnvelope();

        $this->assertStringContainsString('soapenv:Envelope', $soapXml);
        $this->assertStringContainsString('sum:ModificacionFactura', $soapXml);
        $this->assertStringContainsString('sf:RegistroAnulacion', $soapXml);
        $this->assertStringContainsString('sf:RegistroModificacion', $soapXml);
        $this->assertStringContainsString('99999910G', $soapXml);
        $this->assertStringContainsString('TEST0033343436', $soapXml);
        $this->assertStringContainsString('Modified Company', $soapXml);
        $this->assertStringContainsString('42', $soapXml);
        $this->assertStringContainsString('242', $soapXml);
    }

    public function test_invoice_can_create_modification()
    {
        // Create original invoice
        $originalInvoice = new Invoice();
        $originalInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Original Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Original invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('ORIGINAL_HASH');

        // Add emitter to original invoice
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('99999910G')
            ->setRazonSocial('Original Company');
        $originalInvoice->setTercero($emisor);

        // Add sistema informatico to original invoice
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $originalInvoice->setSistemaInformatico($sistema);

        // Create modified invoice
        $modifiedInvoice = new Invoice();
        $modifiedInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Modified Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Modified invoice')
            ->setCuotaTotal(42.00)
            ->setImporteTotal(242.00)
            ->setFechaHoraHusoGenRegistro('2025-01-02T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('MODIFIED_HASH');

        // Add emitter to modified invoice
        $emisorModificado = new PersonaFisicaJuridica();
        $emisorModificado
            ->setNif('99999910G')
            ->setRazonSocial('Modified Company');
        $modifiedInvoice->setTercero($emisorModificado);

        // Add sistema informatico to modified invoice
        $modifiedInvoice->setSistemaInformatico($sistema);

        // Create modification using the invoice method
        $modification = $originalInvoice->createModification($modifiedInvoice);

        $this->assertInstanceOf(InvoiceModification::class, $modification);
        
        // Test cancellation record
        $cancellation = $modification->getRegistroAnulacion();
        $this->assertEquals('99999910G', $cancellation->getIdEmisorFactura());
        $this->assertEquals('TEST0033343436', $cancellation->getNumSerieFactura());
        $this->assertEquals('1', $cancellation->getMotivoAnulacion());

        // Test modification record
        $modificationRecord = $modification->getRegistroModificacion();
        $this->assertEquals('Modified Company', $modificationRecord->getNombreRazonEmisor());
        $this->assertEquals(42.00, $modificationRecord->getCuotaTotal());
        $this->assertEquals(242.00, $modificationRecord->getImporteTotal());
    }

    public function test_invoice_can_create_cancellation()
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Test Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Test invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('TEST_HASH');

        // Add emitter
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('99999910G')
            ->setRazonSocial('Test Company');
        $invoice->setTercero($emisor);

        $cancellation = $invoice->createCancellation();

        $this->assertInstanceOf(RegistroAnulacion::class, $cancellation);
        $this->assertEquals('99999910G', $cancellation->getIdEmisorFactura());
        $this->assertEquals('TEST0033343436', $cancellation->getNumSerieFactura());
        $this->assertEquals('1', $cancellation->getMotivoAnulacion());
    }

    public function test_invoice_can_create_modification_record()
    {
        $invoice = new Invoice();
        $invoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Test Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Test invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('TEST_HASH');

        // Add emitter
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('99999910G')
            ->setRazonSocial('Test Company');
        $invoice->setTercero($emisor);

        // Add sistema informatico
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $invoice->setSistemaInformatico($sistema);

        $modificationRecord = $invoice->createModificationRecord();

        $this->assertInstanceOf(RegistroModificacion::class, $modificationRecord);
        $this->assertEquals('1.0', $modificationRecord->getIdVersion());
        $this->assertEquals('TEST0033343436', $modificationRecord->getIdFactura());
        $this->assertEquals('Test Company', $modificationRecord->getNombreRazonEmisor());
        $this->assertEquals('F1', $modificationRecord->getTipoFactura());
        $this->assertEquals(21.00, $modificationRecord->getCuotaTotal());
        $this->assertEquals(121.00, $modificationRecord->getImporteTotal());
    }

    public function test_modification_xml_structure_matches_aeat_requirements()
    {
        // Create original invoice
        $originalInvoice = new Invoice();
        $originalInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Original Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Original invoice')
            ->setCuotaTotal(21.00)
            ->setImporteTotal(121.00)
            ->setFechaHoraHusoGenRegistro('2025-01-01T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('ORIGINAL_HASH');

        // Add emitter to original invoice
        $emisor = new PersonaFisicaJuridica();
        $emisor
            ->setNif('99999910G')
            ->setRazonSocial('Original Company');
        $originalInvoice->setTercero($emisor);

        // Add sistema informatico to original invoice
        $sistema = new SistemaInformatico();
        $sistema
            ->setNombreRazon('Sistema de Facturación')
            ->setNif('A39200019')
            ->setNombreSistemaInformatico('InvoiceNinja')
            ->setIdSistemaInformatico('77')
            ->setVersion('1.0.03')
            ->setNumeroInstalacion('383');
        $originalInvoice->setSistemaInformatico($sistema);

        // Create modified invoice
        $modifiedInvoice = new Invoice();
        $modifiedInvoice
            ->setIdVersion('1.0')
            ->setIdFactura('TEST0033343436')
            ->setNombreRazonEmisor('Modified Company')
            ->setTipoFactura('F1')
            ->setDescripcionOperacion('Modified invoice')
            ->setCuotaTotal(42.00)
            ->setImporteTotal(242.00)
            ->setFechaHoraHusoGenRegistro('2025-01-02T12:00:00')
            ->setTipoHuella('01')
            ->setHuella('MODIFIED_HASH');

        // Add emitter to modified invoice
        $emisorModificado = new PersonaFisicaJuridica();
        $emisorModificado
            ->setNif('99999910G')
            ->setRazonSocial('Modified Company');
        $modifiedInvoice->setTercero($emisorModificado);

        // Add sistema informatico to modified invoice
        $modifiedInvoice->setSistemaInformatico($sistema);

        // Create modification
        $modification = InvoiceModification::createFromInvoice($originalInvoice, $modifiedInvoice);

        // Generate SOAP envelope
        $soapXml = $modification->toSoapEnvelope();

        // Verify the XML structure matches AEAT requirements
        $this->assertStringContainsString('<soapenv:Envelope', $soapXml);
        $this->assertStringContainsString('<soapenv:Header', $soapXml);
        $this->assertStringContainsString('<soapenv:Body', $soapXml);
        $this->assertStringContainsString('<sum:ModificacionFactura', $soapXml);
        $this->assertStringContainsString('<sf:RegistroAnulacion', $soapXml);
        $this->assertStringContainsString('<sf:RegistroModificacion', $soapXml);
        
        // Verify cancellation structure
        $this->assertStringContainsString('<sf:IDFactura', $soapXml);
        $this->assertStringContainsString('<sf:IDEmisorFactura>99999910G</sf:IDEmisorFactura>', $soapXml);
        $this->assertStringContainsString('<sf:NumSerieFactura>TEST0033343436</sf:NumSerieFactura>', $soapXml);
        $this->assertStringContainsString('<sf:MotivoAnulacion>1</sf:MotivoAnulacion>', $soapXml);
        
        // Verify modification structure
        $this->assertStringContainsString('<sf:NombreRazonEmisor>Modified Company</sf:NombreRazonEmisor>', $soapXml);
        $this->assertStringContainsString('<sf:CuotaTotal>42</sf:CuotaTotal>', $soapXml);
        $this->assertStringContainsString('<sf:ImporteTotal>242</sf:ImporteTotal>', $soapXml);
    }
} 
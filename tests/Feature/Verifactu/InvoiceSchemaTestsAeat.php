<?php

namespace Tests\Feature\Verifactu;

use PHPUnit\Framework\TestCase;
use App\Services\EDocument\Standards\Verifactu\VerifactuClient;
use App\Services\EDocument\Standards\Verifactu\Types\Cabecera;
use App\Services\EDocument\Standards\Verifactu\Types\Desglose;
use App\Services\EDocument\Standards\Verifactu\Types\DesgloseRectificacion;
use App\Services\EDocument\Standards\Verifactu\Types\Destinatarios;
use App\Services\EDocument\Standards\Verifactu\Types\DetalleDesglose;
use App\Services\EDocument\Standards\Verifactu\Types\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Types\IDDestinatario;
use App\Services\EDocument\Standards\Verifactu\Types\IDFactura;
use App\Services\EDocument\Standards\Verifactu\Types\IDFacturaAR;
use App\Services\EDocument\Standards\Verifactu\Types\IDOtro;
use App\Services\EDocument\Standards\Verifactu\Types\ObligadoEmision;
use App\Services\EDocument\Standards\Verifactu\Types\RegFactuSistemaFacturacion;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFactura;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionAnulacion;
use App\Services\EDocument\Standards\Verifactu\Types\SistemaInformatico;

class InvoiceSchemaTestsAeat extends TestCase
{
    private VerifactuClient $client;
    private const TEST_COMPANY_NIF = 'B12345678';
    private const TEST_COMPANY_NAME = 'TEST COMPANY SL';
    private const TEST_CLIENT_NIF = 'B87654321';
    private const TEST_CLIENT_NAME = 'TEST CLIENT SL';

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new VerifactuClient(VerifactuClient::MODE_TEST);
    }

    public function testF1StandardInvoice(): void
    {
        $registro = $this->createF1StandardInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
        // Additional assertions could be added based on expected response structure
    }

    public function testF2SimplifiedInvoice(): void
    {
        $registro = $this->createF2SimplifiedInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    public function testF3SubstituteInvoice(): void
    {
        $registro = $this->createF3SubstituteInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    public function testR1CorrectiveInvoiceError(): void
    {
        $registro = $this->createR1CorrectiveInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    public function testR2CorrectiveInvoiceBankruptcy(): void
    {
        $registro = $this->createR2CorrectiveInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    public function testR3CorrectiveInvoiceBadDebt(): void
    {
        $registro = $this->createR3CorrectiveInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    public function testR4CorrectiveInvoiceGeneral(): void
    {
        $registro = $this->createR4CorrectiveInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    public function testR5CorrectiveInvoiceSimplified(): void
    {
        $registro = $this->createR5CorrectiveInvoice();
        $response = $this->client->sendRegistroAlta($registro);
        
        $this->assertNotNull($response);
    }

    private function createF1StandardInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        // Set invoice ID
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('F1-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('F1');
        $registro->setDescripcionOperacion('F1 Standard Invoice - Complete invoice with full customer details');
        
        // Set recipients with full identification
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon(self::TEST_CLIENT_NAME);
        $destinatario->setNIF(self::TEST_CLIENT_NIF);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        // Set breakdown - F1 must include detailed VAT breakdown
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01'); // General regime
        $detalle->setCalificacionOperacion('S1'); // Not exempt
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(100.0);
        $detalle->setCuotaRepercutida(21.0);
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(21.0);
        $registro->setImporteTotal(121.0);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createF2SimplifiedInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('F2-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('F2');
        $registro->setDescripcionOperacion('F2 Simplified Invoice - No customer identification required');
        
        // F2 invoices may not identify the recipient or have minimal identification
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon('CONSUMIDOR FINAL');
        // For F2, we can use IDOtro instead of NIF for non-Spanish customers
        $idOtro = new IDOtro();
        $idOtro->setIDType('04'); // Legal Entity ID
        $idOtro->setID('NO-IDENTIFICADO');
        $destinatario->setIDOtro($idOtro);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        // Simplified breakdown - amount must be ≤ 3000€
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(50.0); // Keep under 3000€ limit
        $detalle->setCuotaRepercutida(10.5);
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(10.5);
        $registro->setImporteTotal(60.5);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createF3SubstituteInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('F3-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('F3');
        $registro->setDescripcionOperacion('F3 Substitute Invoice - Replaces simplified invoices with full details');
        
        // F3 requires full customer identification (similar to F1)
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon(self::TEST_CLIENT_NAME);
        $destinatario->setNIF(self::TEST_CLIENT_NIF);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(200.0);
        $detalle->setCuotaRepercutida(42.0);
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(42.0);
        $registro->setImporteTotal(242.0);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createR1CorrectiveInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('R1-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('R1');
        $registro->setDescripcionOperacion('R1 Corrective Invoice - Error based on law and Art. 80 LIVA');
        
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon(self::TEST_CLIENT_NAME);
        $destinatario->setNIF(self::TEST_CLIENT_NIF);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        // R1 corrective invoice with rectified amounts
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(-50.0); // Negative amount for correction
        $detalle->setCuotaRepercutida(-10.5);
        
        // Add rectification breakdown
        $desgloseRectificacion = new DesgloseRectificacion();
        $desgloseRectificacion->setBaseRectificada(100.0); // Original amount being corrected
        $desgloseRectificacion->setCuotaRectificada(21.0);
        $detalle->setDesgloseRectificacion($desgloseRectificacion);
        
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(-10.5);
        $registro->setImporteTotal(-60.5);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createR2CorrectiveInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('R2-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('R2');
        $registro->setDescripcionOperacion('R2 Corrective Invoice - Customer bankruptcy (Art. 80.Three LIVA)');
        
        // R2 requires Spanish NIF for customer identification
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon(self::TEST_CLIENT_NAME . ' (EN CONCURSO)');
        $destinatario->setNIF(self::TEST_CLIENT_NIF);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(150.0); // Base remains, VAT reduced to 0
        $detalle->setCuotaRepercutida(0.0); // VAT eliminated due to bankruptcy
        
        $desgloseRectificacion = new DesgloseRectificacion();
        $desgloseRectificacion->setBaseRectificada(150.0);
        $desgloseRectificacion->setCuotaRectificada(31.5); // Original VAT being corrected
        $detalle->setDesgloseRectificacion($desgloseRectificacion);
        
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(0.0);
        $registro->setImporteTotal(150.0);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createR3CorrectiveInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('R3-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('R3');
        $registro->setDescripcionOperacion('R3 Corrective Invoice - Bad debt for VAT refund (Art. 80.Four LIVA)');
        
        // R3 requires Spanish NIF for customer identification
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon(self::TEST_CLIENT_NAME . ' (CREDITO INCOBRABLE)');
        $destinatario->setNIF(self::TEST_CLIENT_NIF);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(300.0); // Base remains
        $detalle->setCuotaRepercutida(0.0); // VAT refund due to bad debt
        
        $desgloseRectificacion = new DesgloseRectificacion();
        $desgloseRectificacion->setBaseRectificada(300.0);
        $desgloseRectificacion->setCuotaRectificada(63.0); // Original VAT being refunded
        $detalle->setDesgloseRectificacion($desgloseRectificacion);
        
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(0.0);
        $registro->setImporteTotal(300.0);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createR4CorrectiveInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('R4-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('R4');
        $registro->setDescripcionOperacion('R4 General Corrective Invoice - Other corrections not covered by R1-R3');
        
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon(self::TEST_CLIENT_NAME);
        $destinatario->setNIF(self::TEST_CLIENT_NIF);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(25.0); // Adjustment amount
        $detalle->setCuotaRepercutida(5.25);
        
        $desgloseRectificacion = new DesgloseRectificacion();
        $desgloseRectificacion->setBaseRectificada(200.0); // Original amount
        $desgloseRectificacion->setCuotaRectificada(42.0);
        $detalle->setDesgloseRectificacion($desgloseRectificacion);
        
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(5.25);
        $registro->setImporteTotal(30.25);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function createR5CorrectiveInvoice(): RegistroAlta
    {
        $registro = new RegistroAlta();
        
        $registro->setIDVersion('1.0');
        
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $idFactura->setNumSerieFactura('R5-TEST-001');
        $idFactura->setFechaExpedicionFactura(date('d-m-Y'));
        $registro->setIDFactura($idFactura);
        
        $registro->setNombreRazonEmisor(self::TEST_COMPANY_NAME);
        $registro->setTipoFactura('R5');
        $registro->setDescripcionOperacion('R5 Corrective Invoice - Correction of simplified invoices (F2)');
        
        // R5 corrects F2 invoices, so minimal customer identification like F2
        $destinatarios = new Destinatarios();
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon('CONSUMIDOR FINAL (CORRECCION)');
        $idOtro = new IDOtro();
        $idOtro->setIDType('04');
        $idOtro->setID('NO-IDENTIFICADO-CORR');
        $destinatario->setIDOtro($idOtro);
        $destinatarios->addIDDestinatario($destinatario);
        $registro->setDestinatarios($destinatarios);
        
        $desglose = new Desglose();
        $detalle = new DetalleDesglose();
        $detalle->setClaveRegimen('01');
        $detalle->setCalificacionOperacion('S1');
        $detalle->setTipoImpositivo(21.0);
        $detalle->setBaseImponibleOimporteNoSujeto(-10.0); // Correction to F2 invoice
        $detalle->setCuotaRepercutida(-2.1);
        
        $desgloseRectificacion = new DesgloseRectificacion();
        $desgloseRectificacion->setBaseRectificada(50.0); // Original F2 amount
        $desgloseRectificacion->setCuotaRectificada(10.5);
        $detalle->setDesgloseRectificacion($desgloseRectificacion);
        
        $desglose->addDetalleDesglose($detalle);
        $registro->setDesglose($desglose);
        
        $registro->setCuotaTotal(-2.1);
        $registro->setImporteTotal(-12.1);
        
        $this->setSistemaInformatico($registro);
        $this->setCommonFields($registro);
        
        return $registro;
    }

    private function setSistemaInformatico(RegistroAlta $registro): void
    {
        $sistemaInformatico = new SistemaInformatico();
        $sistemaInformatico->setNombreRazon('INVOICE NINJA TEST SYSTEM');
        $sistemaInformatico->setNIF(self::TEST_COMPANY_NIF);
        $sistemaInformatico->setNombreSistemaInformatico('InvoiceNinja Verifactu');
        $sistemaInformatico->setIdSistemaInformatico('INV-NINJA-001');
        $sistemaInformatico->setVersion('1.0.0');
        $sistemaInformatico->setNumeroInstalacion('001');
        $sistemaInformatico->setTipoUsoPosibleSoloVerifactu('S');
        $sistemaInformatico->setTipoUsoPosibleMultiOT('N');
        $sistemaInformatico->setIndicadorMultiplesOT('N');
        
        $registro->setSistemaInformatico($sistemaInformatico);
    }

    private function setCommonFields(RegistroAlta $registro): void
    {
        $registro->setFechaHoraHusoGenRegistro(date('Y-m-d\TH:i:sP'));
        $registro->setTipoHuella('01');
        $registro->setHuella('TEST-HASH-' . uniqid());
        
        // Add encadenamiento (chaining) for blockchain
        $encadenamiento = new Encadenamiento();
        $registroAnterior = new IDFacturaAR();
        $registroAnterior->setIDEmisorFactura(self::TEST_COMPANY_NIF);
        $registroAnterior->setNumSerieFactura('PREV-001');
        $registroAnterior->setFechaExpedicionFactura(date('d-m-Y', strtotime('-1 day')));
        $registroAnterior->setHuella('PREVIOUS-HASH-' . uniqid());
        $encadenamiento->setRegistroAnterior($registroAnterior);
        $registro->setEncadenamiento($encadenamiento);
    }

    public function testAllInvoiceTypesSerializeSuccessfully(): void
    {
        $invoiceTypes = [
            'F1' => 'createF1StandardInvoice',
            'F2' => 'createF2SimplifiedInvoice', 
            'F3' => 'createF3SubstituteInvoice',
            'R1' => 'createR1CorrectiveInvoice',
            'R2' => 'createR2CorrectiveInvoice',
            'R3' => 'createR3CorrectiveInvoice',
            'R4' => 'createR4CorrectiveInvoice',
            'R5' => 'createR5CorrectiveInvoice'
        ];

        foreach ($invoiceTypes as $type => $method) {
            $registro = $this->$method();
            
            // Verify the invoice type is set correctly
            $this->assertEquals($type, $registro->getTipoFactura());
            
            // Verify the registro can be serialized without errors
            try {
                $response = $this->client->sendRegistroAlta($registro);
                $this->assertNotNull($response, "Failed to serialize invoice type: $type");
            } catch (\Exception $e) {
                $this->fail("Exception occurred while serializing invoice type $type: " . $e->getMessage());
            }
        }
    }

    public function testInvoiceTypeValidation(): void
    {
        $validTypes = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3', 'R4', 'R5'];
        
        foreach ($validTypes as $type) {
            $registro = new RegistroAlta();
            $registro->setTipoFactura($type);
            $this->assertEquals($type, $registro->getTipoFactura());
        }
    }

    public function testCorrectiveInvoicesIncludeRectificationData(): void
    {
        $correctiveTypes = ['R1', 'R2', 'R3', 'R4', 'R5'];
        
        foreach ($correctiveTypes as $type) {
            $method = 'create' . $type . 'CorrectiveInvoice';
            $registro = $this->$method();
            
            $desglose = $registro->getDesglose();
            $detalles = $desglose->getDetalleDesglose();
            
            $this->assertNotEmpty($detalles, "Corrective invoice $type should have breakdown details");
            
            foreach ($detalles as $detalle) {
                if ($detalle->getDesgloseRectificacion()) {
                    $rectificacion = $detalle->getDesgloseRectificacion();
                    $this->assertNotNull($rectificacion->getBaseRectificada(), 
                        "Corrective invoice $type should have rectified base");
                    $this->assertNotNull($rectificacion->getCuotaRectificada(), 
                        "Corrective invoice $type should have rectified quota");
                }
            }
        }
    }
} 
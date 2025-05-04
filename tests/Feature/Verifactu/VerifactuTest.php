<?php

namespace Tests\Feature\Verifactu;

use App\Services\EDocument\Standards\Verifactu\Types\Detalle;
use App\Services\EDocument\Standards\Verifactu\Types\DetalleDesglose;
use App\Services\EDocument\Standards\Verifactu\Types\Desglose;
use App\Services\EDocument\Standards\Verifactu\Types\DesgloseRectificacion;
use App\Services\EDocument\Standards\Verifactu\Types\Destinatarios;
use App\Services\EDocument\Standards\Verifactu\Types\Encadenamiento;
use App\Services\EDocument\Standards\Verifactu\Types\IDDestinatario;
use App\Services\EDocument\Standards\Verifactu\Types\IDFactura;
use App\Services\EDocument\Standards\Verifactu\Types\IDFacturaAR;
use App\Services\EDocument\Standards\Verifactu\Types\IDFacturaExpedida;
use App\Services\EDocument\Standards\Verifactu\Types\Incidencia;
use App\Services\EDocument\Standards\Verifactu\Types\ObligadoEmision;
use App\Services\EDocument\Standards\Verifactu\Types\OperacionExenta;
use App\Services\EDocument\Standards\Verifactu\Types\PersonaFisicaJuridica;
use App\Services\EDocument\Standards\Verifactu\Types\PersonaFisicaJuridicaES;
use App\Services\EDocument\Standards\Verifactu\Types\RechazoPrevio;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFactura;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroFacturacionAnulacion;
use App\Services\EDocument\Standards\Verifactu\Types\SistemaInformatico;
use App\Services\EDocument\Standards\Verifactu\Types\Subsanacion;
use PHPUnit\Framework\TestCase;

class VerifactuTest extends TestCase
{
    public function testSubsanacionValidation()
    {
        $subsanacion = new Subsanacion();

        $subsanacion->setNumRegistroAcuerdoFacturacion('12345')
                    ->setFechaRegistroAcuerdoFacturacion('2024-03-20');

        $this->assertEquals('12345', $subsanacion->getNumRegistroAcuerdoFacturacion());
        $this->assertEquals('2024-03-20', $subsanacion->getFechaRegistroAcuerdoFacturacion());

        $this->expectException(\InvalidArgumentException::class);
        $subsanacion->setNumRegistroAcuerdoFacturacion(str_repeat('1', 16)); // Exceeds 15 chars
    }

    public function testRechazoPrevioValidation()
    {
        $rechazoPrevio = new RechazoPrevio();

        $rechazoPrevio->setNumRegistroAcuerdoFacturacion('12345')
                      ->setFechaRegistroAcuerdoFacturacion('2024-03-20')
                      ->setMotivoRechazo('Motivo de prueba');

        $this->assertEquals('12345', $rechazoPrevio->getNumRegistroAcuerdoFacturacion());
        $this->assertEquals('2024-03-20', $rechazoPrevio->getFechaRegistroAcuerdoFacturacion());
        $this->assertEquals('Motivo de prueba', $rechazoPrevio->getMotivoRechazo());

        $this->expectException(\InvalidArgumentException::class);
        $rechazoPrevio->setMotivoRechazo(str_repeat('a', 2001)); // Exceeds 2000 chars
    }

    public function testDetalleValidation()
    {
        $detalle = new Detalle();

        $detalle->setImpuesto('IVA')
                ->setClaveRegimen('01')
                ->setBaseImponibleOimporteNoSujeto(1000.50)
                ->setTipoImpositivo(21.00);

        $this->assertEquals('IVA', $detalle->getImpuesto());
        $this->assertEquals('01', $detalle->getClaveRegimen());
        $this->assertEquals(1000.50, $detalle->getBaseImponibleOimporteNoSujeto());
        $this->assertEquals(21.00, $detalle->getTipoImpositivo());

        $this->expectException(\InvalidArgumentException::class);
        $detalle->setTipoImpositivo(101.00); // Exceeds 100%
    }


    public function testOperacionExentaValidation()
    {
        $operacionExenta = new OperacionExenta(OperacionExenta::E1);
        $this->assertEquals(OperacionExenta::E1, $operacionExenta->getValue());

        $this->expectException(\InvalidArgumentException::class);
        new OperacionExenta('INVALID_VALUE');
    }

    public function testIDFacturaARValidation()
    {
        $idFactura = new IDFacturaAR();

        $idFactura->setIDEmisorFactura('B12345678')
                  ->setNumSerieFactura('FACT2024-001')
                  ->setFechaExpedicionFactura('20-03-2024');

        $this->assertEquals('B12345678', $idFactura->getIDEmisorFactura());
        $this->assertEquals('FACT2024-001', $idFactura->getNumSerieFactura());
        $this->assertEquals('20-03-2024', $idFactura->getFechaExpedicionFactura());

        $this->expectException(\InvalidArgumentException::class);
        $idFactura->setFechaExpedicionFactura('2024-03-20'); // Wrong format
    }

    public function testRegistroFacturaValidation()
    {
        $registroFactura = new RegistroFactura();
        $registroAlta = new RegistroAlta();

        $registroFactura->setRegistroAlta($registroAlta);
        $this->assertInstanceOf(RegistroAlta::class, $registroFactura->getRegistroAlta());

        $registroAnulacion = new RegistroFacturacionAnulacion();

        $this->expectException(\InvalidArgumentException::class);
        $registroFactura->setRegistroAnulacion($registroAnulacion); // Cannot set both
    }

    public function testObligadoEmisionValidation()
    {
        $obligado = new ObligadoEmision();
        $obligado->setNIF('B12345678');
        $obligado->setNombreRazon('Empresa Test');

        $this->assertEquals('B12345678', $obligado->getNIF());
        $this->assertEquals('Empresa Test', $obligado->getNombreRazon());
    }

    public function testObligadoEmisionEmptyNombreRazon()
    {
        $obligado = new ObligadoEmision();
        $this->expectException(\InvalidArgumentException::class);
        $obligado->setNombreRazon('');
    }

    public function testObligadoEmisionEmptyNIF()
    {
        $obligado = new ObligadoEmision();
        $this->expectException(\InvalidArgumentException::class);
        $obligado->setNIF('');
    }

    public function testObligadoEmisionInvalidNIF()
    {
        $obligado = new ObligadoEmision();
        $this->expectException(\InvalidArgumentException::class);
        $obligado->setNIF('invalid');
    }

    public function testCreateCompleteRegistroFactura()
    {
        // Create ObligadoEmision
        $obligadoEmision = new ObligadoEmision();
        $obligadoEmision->setNombreRazon('XXXXX')
                        ->setNIF('A12345678');

        // Create IDFactura
        $idFactura = new IDFactura();
        $idFactura->setIDEmisorFactura('B12345678')
                  ->setNumSerieFactura('12345')
                  ->setFechaExpedicionFactura('13-09-2024');

        // Create Destinatario
        $destinatario = new IDDestinatario();
        $destinatario->setNombreRazon('YYYY')
                     ->setNIF('C12345678');

        // Create Destinatarios collection
        $destinatarios = new Destinatarios();
        $destinatarios->addIDDestinatario($destinatario);

        // Create Detalles for Desglose
        $detalle1 = new DetalleDesglose();
        $detalle1->setClaveRegimen('01')
                 ->setCalificacionOperacion('S1')
                 ->setTipoImpositivo(4.0)
                 ->setBaseImponibleOimporteNoSujeto(10.0)
                 ->setCuotaRepercutida(0.4);

        $detalle2 = new DetalleDesglose();
        $detalle2->setClaveRegimen('01')
                 ->setCalificacionOperacion('S1')
                 ->setTipoImpositivo(21.0)
                 ->setBaseImponibleOimporteNoSujeto(100.0)
                 ->setCuotaRepercutida(21.0);

        // Create Desglose
        $desglose = new Desglose();
        $desglose->addDetalleDesglose($detalle1);
        $desglose->addDetalleDesglose($detalle2);

        // Create RegistroAnterior for Encadenamiento
        $registroAnterior = new IDFacturaAR();
        $registroAnterior->setIDEmisorFactura('E12345678')
                        ->setNumSerieFactura('44')
                        ->setFechaExpedicionFactura('13-09-2024');

        // Create Encadenamiento
        $encadenamiento = new Encadenamiento();
        $encadenamiento->setRegistroAnterior($registroAnterior)
                      ->setHuellaRegistroAnterior('HuellaRegistroAnterior');

        // Create SistemaInformatico
        $sistemaInformatico = new SistemaInformatico();
        $sistemaInformatico->setNombreRazon('SSSS')
                          ->setNIF('D12345678')
                          ->setNombreSistemaInformatico('NombreSistemaInformatico')
                          ->setIdSistemaInformatico('77')
                          ->setVersion('1.0.03')
                          ->setNumeroInstalacion('383')
                          ->setTipoUsoPosibleSoloVerifactu('N')
                          ->setTipoUsoPosibleMultiOT('S')
                          ->setIndicadorMultiplesOT('S');

        // Create RegistroAlta
        $registroAlta = new RegistroAlta();
        $registroAlta->setIDVersion('1.0')
                     ->setIDFactura($idFactura)
                     ->setNombreRazonEmisor('XXXXX')
                     ->setTipoFactura('F1')
                     ->setDescripcionOperacion('Descripc')
                     ->setDestinatarios($destinatarios)
                     ->setDesglose($desglose)
                     ->setCuotaTotal(21.4)
                     ->setImporteTotal(131.4)
                     ->setEncadenamiento($encadenamiento)
                     ->setSistemaInformatico($sistemaInformatico)
                     ->setFechaHoraHusoGenRegistro('2024-09-13T19:20:30+01:00')
                     ->setTipoHuella('01')
                     ->setHuella('Huella');

        // Create RegistroFactura
        $registroFactura = new RegistroFactura();
        $registroFactura->setRegistroAlta($registroAlta);

        // Assertions
        $this->assertEquals('1.0', $registroAlta->getIDVersion());
        $this->assertEquals('XXXXX', $registroAlta->getNombreRazonEmisor());
        $this->assertEquals('F1', $registroAlta->getTipoFactura());
        $this->assertEquals('Descripc', $registroAlta->getDescripcionOperacion());
        $this->assertEquals(21.4, $registroAlta->getCuotaTotal());
        $this->assertEquals(131.4, $registroAlta->getImporteTotal());
        $this->assertEquals('01', $registroAlta->getTipoHuella());
        $this->assertEquals('Huella', $registroAlta->getHuella());

        // Test nested objects
        $this->assertEquals('B12345678', $registroAlta->getIDFactura()->getIDEmisorFactura());
        $this->assertEquals('12345', $registroAlta->getIDFactura()->getNumSerieFactura());
        $this->assertEquals('13-09-2024', $registroAlta->getIDFactura()->getFechaExpedicionFactura());

        // Test Destinatarios
        $destinatarios = $registroAlta->getDestinatarios();
        $this->assertEquals('YYYY', $destinatarios->getIDDestinatario()[0]->getNombreRazon());
        $this->assertEquals('C12345678', $destinatarios->getIDDestinatario()[0]->getNIF());

        // Test Desglose
        $detalles = $registroAlta->getDesglose()->getDetalleDesglose();
        $this->assertEquals(4.0, $detalles[0]->getTipoImpositivo());
        $this->assertEquals(10.0, $detalles[0]->getBaseImponibleOimporteNoSujeto());
        $this->assertEquals(0.4, $detalles[0]->getCuotaRepercutida());
        $this->assertEquals(21.0, $detalles[1]->getTipoImpositivo());
        $this->assertEquals(100.0, $detalles[1]->getBaseImponibleOimporteNoSujeto());
        $this->assertEquals(21.0, $detalles[1]->getCuotaRepercutida());

        // Test SistemaInformatico
        $this->assertEquals('SSSS', $registroAlta->getSistemaInformatico()->getNombreRazon());
        $this->assertEquals('D12345678', $registroAlta->getSistemaInformatico()->getNIF());
        $this->assertEquals('77', $registroAlta->getSistemaInformatico()->getIdSistemaInformatico());
    }
}

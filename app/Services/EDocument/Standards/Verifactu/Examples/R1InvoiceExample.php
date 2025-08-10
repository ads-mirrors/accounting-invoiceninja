<?php

namespace App\Services\EDocument\Standards\Verifactu\Examples;

use App\Services\EDocument\Standards\Verifactu\Models\Invoice;
use App\Services\EDocument\Standards\Verifactu\Models\IDFactura;
use App\Services\EDocument\Standards\Verifactu\Models\FacturaRectificativa;

/**
 * Example demonstrating how to create R1 (rectificative) invoices
 * with proper conditional logic for ImporteRectificacion
 */
class R1InvoiceExample
{
    /**
     * Example 1: Create a substitutive rectification invoice (R1 with TipoRectificativa = 'S')
     * This requires ImporteRectificacion to be set
     */
    public static function createSubstitutiveRectification(): Invoice
    {
        $invoice = new Invoice();
        
        // Set basic invoice information
        $invoice->setIdVersion('1.0')
                ->setIdFactura(new IDFactura('A39200019', 'TEST0033343444', '09-08-2025'))
                ->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS')
                ->setDescripcionOperacion('Rectificación sustitutiva de factura anterior')
                ->setCuotaTotal(46.08)
                ->setImporteTotal(141.08)
                ->setFechaHoraHusoGenRegistro('2025-08-09T22:33:13+02:00')
                ->setTipoHuella('01')
                ->setHuella('C8053880DA04439862AEE429EB7AF6CF9F2D00141896B0646ED5BF7A2C482623');
        
        // Make it a substitutive rectification (R1 with S type)
        // This automatically sets TipoFactura to 'R1' and TipoRectificativa to 'S'
        $invoice->makeSubstitutiveRectificationWithAmount(
            100.00, // ImporteRectificacion - required for substitutive rectifications
            'Rectificación sustitutiva de factura anterior'
        );
        
        // Set up the rectified invoice information
        $invoice->setRectifiedInvoice(
            'A39200019',           // NIF of rectified invoice
            'TEST0033343443',      // Series number of rectified invoice
            '09-08-2025'           // Date of rectified invoice
        );
        
        return $invoice;
    }
    
    /**
     * Example 2: Create a complete rectification invoice (R1 with TipoRectificativa = 'I')
     * ImporteRectificacion is optional but recommended
     */
    public static function createCompleteRectification(): Invoice
    {
        $invoice = new Invoice();
        
        // Set basic invoice information
        $invoice->setIdVersion('1.0')
                ->setIdFactura(new IDFactura('A39200019', 'TEST0033343445', '09-08-2025'))
                ->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS')
                ->setDescripcionOperacion('Rectificación completa de factura anterior')
                ->setCuotaTotal(46.08)
                ->setImporteTotal(141.08)
                ->setFechaHoraHusoGenRegistro('2025-08-09T22:33:13+02:00')
                ->setTipoHuella('01')
                ->setHuella('C8053880DA04439862AEE429EB7AF6CF9F2D00141896B0646ED5BF7A2C482623');
        
        // Make it a complete rectification (R1 with I type)
        // ImporteRectificacion is optional for complete rectifications
        $invoice->makeCompleteRectification('Rectificación completa de factura anterior');
        
        // Optionally set ImporteRectificacion (recommended but not mandatory)
        $invoice->setImporteRectificacion(50.00);
        
        // Set up the rectified invoice information
        $invoice->setRectifiedInvoice(
            'A39200019',           // NIF of rectified invoice
            'TEST0033343443',      // Series number of rectified invoice
            '09-08-2025'           // Date of rectified invoice
        );
        
        return $invoice;
    }
    
    /**
     * Example 3: Create a substitutive rectification with automatic ImporteRectificacion calculation
     */
    public static function createSubstitutiveRectificationWithAutoCalculation(): Invoice
    {
        $invoice = new Invoice();
        
        // Set basic invoice information
        $invoice->setIdVersion('1.0')
                ->setIdFactura(new IDFactura('A39200019', 'TEST0033343446', '09-08-2025'))
                ->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS')
                ->setDescripcionOperacion('Rectificación sustitutiva con cálculo automático')
                ->setCuotaTotal(46.08)
                ->setImporteTotal(141.08)
                ->setFechaHoraHusoGenRegistro('2025-08-09T22:33:13+02:00')
                ->setTipoHuella('01')
                ->setHuella('C8053880DA04439862AEE429EB7AF6CF9F2D00141896B0646ED5BF7A2C482623');
        
        // Calculate ImporteRectificacion automatically from the difference
        $originalAmount = 200.00;  // Original invoice amount
        $newAmount = 141.08;       // New invoice amount
        $invoice->makeSubstitutiveRectificationFromDifference(
            $originalAmount,
            $newAmount,
            'Rectificación sustitutiva con cálculo automático'
        );
        
        // Set up the rectified invoice information
        $invoice->setRectifiedInvoice(
            'A39200019',           // NIF of rectified invoice
            'TEST0033343443',      // Series number of rectified invoice
            '09-08-2025'           // Date of rectified invoice
        );
        
        return $invoice;
    }
    
    /**
     * Example 4: Step-by-step creation of a substitutive rectification
     */
    public static function createSubstitutiveRectificationStepByStep(): Invoice
    {
        $invoice = new Invoice();
        
        // Step 1: Set basic invoice information
        $invoice->setIdVersion('1.0')
                ->setIdFactura(new IDFactura('A39200019', 'TEST0033343447', '09-08-2025'))
                ->setNombreRazonEmisor('CERTIFICADO FISICA PRUEBAS')
                ->setDescripcionOperacion('Rectificación sustitutiva paso a paso')
                ->setCuotaTotal(46.08)
                ->setImporteTotal(141.08)
                ->setFechaHoraHusoGenRegistro('2025-08-09T22:33:13+02:00')
                ->setTipoHuella('01')
                ->setHuella('C8053880DA04439862AEE429EB7AF6CF9F2D00141896B0646ED5BF7A2C482623');
        
        // Step 2: Set invoice type to rectificative
        $invoice->setTipoFactura(Invoice::TIPO_FACTURA_RECTIFICATIVA);
        
        // Step 3: Set rectification type to substitutive
        $invoice->setTipoRectificativa(Invoice::TIPO_RECTIFICATIVA_SUSTITUTIVA);
        
        // Step 4: Set ImporteRectificacion (mandatory for substitutive)
        $invoice->setImporteRectificacion(100.00);
        
        // Step 5: Set up the rectified invoice information
        $invoice->setRectifiedInvoice(
            'A39200019',           // NIF of rectified invoice
            'TEST0033343443',      // Series number of rectified invoice
            '09-08-2025'           // Date of rectified invoice
        );
        
        return $invoice;
    }
    
    /**
     * Validate and generate XML for an R1 invoice
     */
    public static function generateXml(Invoice $invoice): string
    {
        try {
            // Validate the invoice first
            $invoice->validate();
            
            // Generate XML
            $xml = $invoice->toXmlString();
            
            return $xml;
        } catch (\InvalidArgumentException $e) {
            throw new \RuntimeException('Invoice validation failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \RuntimeException('XML generation failed: ' . $e->getMessage());
        }
    }
}

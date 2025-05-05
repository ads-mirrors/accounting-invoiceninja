<?php

namespace Tests\Feature\Verifactu;
use Tests\TestCase;
use App\Models\Invoice;
use Tests\MockAccountData;
use App\Helpers\Invoice\InvoiceSum;
use Database\Factories\InvoiceFactory;
use Symfony\Component\Serializer\Serializer;
use App\Services\EDocument\Standards\Verifactu;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use App\Services\EDocument\Standards\Verifactu\Types\RegistroAlta;
use App\Services\EDocument\Standards\Verifactu\Types\SoapEnvelope;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use App\Services\EDocument\Standards\Verifactu\Types\RegFactuSistemaFacturacion;

class SerializerTest extends TestCase
{
    
    use MockAccountData;
    use DatabaseTransactions;

    public $invoice;
    public $invoice_calc;

    protected function setUp(): void
    {
        parent::setUp();

        $this->makeTestData();

        $this->invoice->line_items = $this->buildLineItems();

        $this->invoice->uses_inclusive_taxes = true;

        $this->invoice_calc = new InvoiceSum($this->invoice);
    }

    public function testDeserialize()
    {
                
        $document = file_get_contents(__DIR__ . '/invoice.xml');

        $verifactu = new Verifactu($this->invoice);

        $serializer = $verifactu->getSerializer();

        $parent_class = SoapEnvelope::class;

        $invoice = $serializer->deserialize($document, $parent_class, 'xml', [\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $this->assertInstanceOf(SoapEnvelope::class, $invoice);

    }

    public function testSerializeXml()
    {

        $document = file_get_contents(__DIR__ . '/invoice.xml');
        
        $verifactu = new Verifactu($this->invoice);

        $serializer = $verifactu->getSerializer();

        $parent_class = SoapEnvelope::class;

        $invoice = $serializer->deserialize($document, $parent_class, 'xml', [\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        $xml = $verifactu->serializeXml($invoice);

        // nlog($xml);
        $this->assertStringContainsString('soapenv:Envelope', $xml);
    }
}
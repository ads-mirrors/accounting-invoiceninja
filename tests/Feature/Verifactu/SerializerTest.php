<?php

namespace Tests\Feature\Verifactu;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use App\Services\EDocument\Standards\Verifactu\Types\SoapEnvelope;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use App\Services\EDocument\Standards\Verifactu\Types\RegFactuSistemaFacturacion;

class SerializerTest extends TestCase
{

    public function testDeserialize()
    {
                
        $document = file_get_contents(__DIR__ . '/invoice.xml');
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        // list of PropertyTypeExtractorInterface (any iterable)
        $typeExtractors = [$reflectionExtractor,$phpDocExtractor];

        // list of PropertyDescriptionExtractorInterface (any iterable)
        $descriptionExtractors = [$phpDocExtractor];

        // list of PropertyInitializableExtractorInterface (any iterable)
        $propertyInitializableExtractors = [$reflectionExtractor];

        $propertyInfo = new PropertyInfoExtractor(
            $propertyInitializableExtractors,
            $descriptionExtractors,
            $typeExtractors,
        );

        $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader());

        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);

        $normalizer = new ObjectNormalizer($classMetadataFactory, $metadataAwareNameConverter, null, $propertyInfo);

        $normalizers = [new DateTimeNormalizer(), $normalizer,  new ArrayDenormalizer()];

        $encoders = [new XmlEncoder(['xml_format_output' => true,\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true]), new JsonEncoder()];

        $serializer = new Serializer($normalizers, $encoders);

        $parent_class = SoapEnvelope::class;

        $invoice = $serializer->deserialize($document, $parent_class, 'xml', [\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer::SKIP_NULL_VALUES => true]);

        // nlog($invoice);
echo(print_r($invoice, true));

    }
}
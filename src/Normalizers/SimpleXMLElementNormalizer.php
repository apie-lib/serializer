<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use SimpleXMLElement;

class SimpleXMLElementNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof SimpleXMLElement;
    }

    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string
    {
        return (string) $object->asXML();
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === SimpleXMLElement::class;
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): SimpleXMLElement
    {
        return new SimpleXMLElement(Utils::toString($object));
    }
}

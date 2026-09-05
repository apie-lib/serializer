<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use DOMAttr;
use DOMDocument;
use DOMElement;
use Psr\Http\Message\UploadedFileInterface;

class DomNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof DOMAttr || $object instanceof DOMElement;
    }

    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string
    {
        if ($object instanceof DOMAttr) {
            return $object->name . '="' . htmlspecialchars($object->value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '"';
        }

        $document = $object->ownerDocument ?? new DOMDocument();
        $node = $object;
        if ($object->ownerDocument === null) {
            $node = $document->importNode($object, true);
            $document->appendChild($node);
        }
        return (string) $document->saveXML($node);
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === DOMAttr::class || $desiredType === DOMElement::class;
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): DOMAttr|DOMElement
    {
        $value = Utils::toString($object);
        if ($desiredType === DOMAttr::class) {
            if (preg_match('/^([A-Za-z_][A-Za-z0-9_.:-]*)="(.*)"$/s', $value, $matches)) {
                return new DOMAttr($matches[1], htmlspecialchars_decode($matches[2], ENT_XML1));
            }
            return new DOMAttr('value', $value);
        }

        $document = new DOMDocument();
        $document->loadXML($value, LIBXML_NONET);
        return $document->documentElement;
    }
}

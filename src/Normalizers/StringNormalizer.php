<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;

class StringNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === 'string';
    }
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): string
    {
        return match (gettype($object)) {
            'string' => $object,
            'boolean' => (string) $object,
            'integer' => (string) $object,
            'double' => (string) $object,
            default => throw new InvalidTypeException($object, 'string'),
        };
    }
}

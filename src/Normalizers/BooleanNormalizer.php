<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;

class BooleanNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType ==='bool' | $desiredType === 'boolean';
    }
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): string
    {
        return match (gettype($object)) {
            'string' => filter_var($object, FILTER_VALIDATE_BOOLEAN),
            'boolean' => $object,
            'integer' => !!$object,
            'double' => !!$object,
            default => throw new InvalidTypeException($object, 'bool'),
        };
    }
}

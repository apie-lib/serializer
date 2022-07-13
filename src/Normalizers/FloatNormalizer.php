<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;

class FloatNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType ==='float' | $desiredType === 'double';
    }
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): string
    {
        return Utils::toFloat($object);
    }
}

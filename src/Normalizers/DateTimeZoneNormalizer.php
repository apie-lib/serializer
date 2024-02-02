<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use DateTimeZone;

final class DateTimeZoneNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof DateTimeZone;
    }

    /**
     * @param DateTimeZone $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string
    {
        return $object->getName();
    }

    public function supportsDenormalization(
        string|int|float|bool|null|ItemList|ItemHashmap $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return is_a($desiredType, DateTimeZone::class, true);
    }

    /**
     * @param class-string<DateTimeZone> $desiredType
     */
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): DateTimeZone
    {
        $object = Utils::toString($object);
        return new $desiredType($object);
    }
}

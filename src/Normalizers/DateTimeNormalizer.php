<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use DateTimeImmutable;
use DateTimeInterface;

final class DateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof DateTimeInterface;
    }

    /**
     * @param DateTimeInterface $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int
    {
        return $object->format(DateTimeInterface::ATOM);
    }

    public function supportsDenormalization(
        string|int|float|bool|null|ItemList|ItemHashmap $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return is_a($desiredType, DateTimeInterface::class, true);
    }

    /**
     * @param class-string<DateTimeInterface> $desiredType
     */
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): DateTimeInterface
    {
        $object = Utils::toString($object);
        if ($desiredType === DateTimeInterface::class) {
            $desiredType = DateTimeImmutable::class;
        }
        return new $desiredType($object);
    }
}

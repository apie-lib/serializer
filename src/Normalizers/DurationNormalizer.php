<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Dto\DurationDto;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\UploadedFileInterface;
use Time\Duration;

class DurationNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof Duration;
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === Duration::class;
    }

    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): ItemHashmap
    {
        return $apieSerializerContext->normalizeAgain(json_decode(json_encode($object), true));
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        if (is_numeric($object)) {
            $object = (float) $object;
            $result = Duration::fromNanoseconds((int) abs(1000 * $object));
            if ($object < 0) {
                $result = $result->negate();
            }
            return $result;
        }
        $array = Utils::toArray($object);
        $dto = $apieSerializerContext->denormalizeFromTypehint($array, ReflectionTypeFactory::createReflectionType(DurationDto::class));

        $result = Duration::fromSeconds($dto->seconds, $dto->nanoseconds);
        if ($dto->negative) {
            $result = $result->negate();
        }
        return $result;
    }
}

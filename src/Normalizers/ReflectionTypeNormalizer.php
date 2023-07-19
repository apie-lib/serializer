<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class ReflectionTypeNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function supportsDenormalization(
        string|int|float|bool|null|ItemList|ItemHashmap $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return in_array(
            $desiredType,
            [
                ReflectionType::class,
                ReflectionNamedType::class,
                ReflectionUnionType::class,
                ReflectionIntersectionType::class
            ]
        );
    }
    public function denormalize(
        string|int|float|bool|null|ItemList|ItemHashmap $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): mixed {
        return ReflectionTypeFactory::createReflectionType(Utils::toString($object));
    }
    public function supportsNormalization(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return $object instanceof ReflectionType;
    }
    public function normalize(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): string|int|float|bool|null|ItemList|ItemHashmap {
        assert($object instanceof ReflectionType);
        return $object instanceof ReflectionNamedType ? $object->getName() : (string) $object;
    }
}

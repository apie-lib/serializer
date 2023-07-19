<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\CompositeValueObject;
use Apie\Core\ValueObjects\CompositeWithOwnValidation;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Exception;
use ReflectionClass;

class ValueObjectNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof ValueObjectInterface;
    }
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap
    {
        $value = $object->toNative();
        if (is_iterable($value)) {
            return $apieSerializerContext->normalizeAgain($value);
        }
        return $value;
    }
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        if (is_a($desiredType, ValueObjectInterface::class, true)) {
            $class = new ReflectionClass($desiredType);
            return $class->implementsInterface(CompositeWithOwnValidation::class)
                ||!in_array(CompositeValueObject::class, $class->getTraitNames());
        }
        return false;
    }
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        try {
            return $desiredType::fromNative($object);
        } catch (Exception $exception) {
            throw ValidationException::createFromArray(['' => $exception]);
        }
    }
}

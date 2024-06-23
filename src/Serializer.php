<?php
namespace Apie\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Metadata\Concerns\UseContextKey;
use Apie\Core\Metadata\MetadataFactory;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Context\NormalizeChildGroup;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Lists\NormalizerList;
use Apie\Serializer\Normalizers\BooleanNormalizer;
use Apie\Serializer\Normalizers\DateTimeNormalizer;
use Apie\Serializer\Normalizers\DateTimeZoneNormalizer;
use Apie\Serializer\Normalizers\EnumNormalizer;
use Apie\Serializer\Normalizers\FloatNormalizer;
use Apie\Serializer\Normalizers\IdentifierNormalizer;
use Apie\Serializer\Normalizers\IntegerNormalizer;
use Apie\Serializer\Normalizers\ItemListNormalizer;
use Apie\Serializer\Normalizers\PaginatedResultNormalizer;
use Apie\Serializer\Normalizers\PolymorphicObjectNormalizer;
use Apie\Serializer\Normalizers\ReflectionTypeNormalizer;
use Apie\Serializer\Normalizers\ResourceNormalizer;
use Apie\Serializer\Normalizers\StringableCompositeValueObjectNormalizer;
use Apie\Serializer\Normalizers\StringNormalizer;
use Apie\Serializer\Normalizers\ValueObjectNormalizer;
use Exception;
use ReflectionClass;
use ReflectionMethod;

class Serializer
{
    use UseContextKey;

    public function __construct(private NormalizerList $normalizers)
    {
    }

    public static function create(): self
    {
        return new self(new NormalizerList([
            new PaginatedResultNormalizer(),
            new IdentifierNormalizer(),
            new StringableCompositeValueObjectNormalizer(),
            new PolymorphicObjectNormalizer(),
            new DateTimeNormalizer(),
            new DateTimeZoneNormalizer(),
            new ResourceNormalizer(),
            new EnumNormalizer(),
            new ValueObjectNormalizer(),
            new StringNormalizer(),
            new IntegerNormalizer(),
            new FloatNormalizer(),
            new BooleanNormalizer(),
            new ItemListNormalizer(),
            new ReflectionTypeNormalizer(),
        ]));
    }

    public function normalize(mixed $object, ApieContext $apieContext, bool $forceDefaultNormalization = false): string|int|float|bool|ItemList|ItemHashmap|null
    {
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        if (!$forceDefaultNormalization) {
            foreach ($this->normalizers->iterateOverNormalizers() as $normalizer) {
                if ($normalizer->supportsNormalization($object, $serializerContext)) {
                    return $normalizer->normalize($object, $serializerContext);
                }
            }
        }
        if (is_array($object)) {
            $count = 0;
            $returnValue = [];
            $isList = true;
            foreach ($object as $key => $value) {
                if ($key === $count) {
                    $count++;
                } else {
                    $isList = false;
                }
                $returnValue[$key] = $serializerContext->normalizeChildElement($key, $value);
            }
            return $isList ? new ItemList($returnValue) : new ItemHashmap($returnValue);
        }
        if (!is_object($object)) {
            return $object;
        }
        $metadata = MetadataFactory::getResultMetadata(new ReflectionClass($object), $apieContext);
        $returnValue = [];

        foreach ($metadata->getHashmap()->filterOnContext($apieContext, getters: true) as $fieldName => $metadata) {
            if ($metadata->isField()) {
                $returnValue[$fieldName] = $serializerContext->normalizeChildElement(
                    $fieldName,
                    $metadata->getValue($object, $apieContext)
                );
            }
        }
        return new ItemHashmap($returnValue);
    }

    public function denormalizeOnMethodCall(string|int|float|bool|ItemList|ItemHashmap|array|null $input, ?object $object, ReflectionMethod $method, ApieContext $apieContext): mixed
    {
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        try {
            $arguments = $serializerContext->denormalizeFromMethod($input, $method);
        } catch (Exception $error) {
            throw ValidationException::createFromArray(['' => $error]);
        }
        return $method->invokeArgs($object, $arguments);
    }

    public function denormalizeNewObject(string|int|float|bool|ItemList|ItemHashmap|array|null $object, string $desiredType, ApieContext $apieContext): mixed
    {
        if (is_array($object)) {
            $isList = false;
            if ($desiredType === 'mixed') {
                $isList = true;
                $count = 0;
                foreach (array_keys($object) as $key) {
                    if ($key === $count) {
                        $count++;
                    } else {
                        $isList = false;
                        break;
                    }
                }
            }
            $object = $isList ? new ItemList($object) : new ItemHashmap($object);
        }
        if ($desiredType === 'mixed') {
            return $object;
        }
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        foreach ($this->normalizers->iterateOverDenormalizers() as $denormalizer) {
            if ($denormalizer->supportsDenormalization($object, $desiredType, $serializerContext)) {
                return $denormalizer->denormalize($object, $desiredType, $serializerContext);
            }
        }
        $refl = new ReflectionClass($desiredType);
        if (!$refl->isInstantiable()) {
            throw new InvalidTypeException($desiredType, 'a instantiable object');
        }
        $metadata = MetadataFactory::getCreationMetadata(
            $refl,
            $apieContext
        );
        $group = new NormalizeChildGroup(
            $serializerContext,
            $metadata
        );
        $normalizedData = $group->buildNormalizedData($refl, Utils::toArray($object));
        return $normalizedData->createNewObject();
    }

    public function denormalizeOnExistingObject(ItemHashmap $object, object $existingObject, ApieContext $apieContext): mixed
    {
        $refl = new ReflectionClass($existingObject);
        $metadata = MetadataFactory::getCreationMetadata(
            $refl,
            $apieContext
        );
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        $metadata = MetadataFactory::getModificationMetadata(
            $refl,
            $apieContext
        );
        $group = new NormalizeChildGroup(
            $serializerContext,
            $metadata
        );
        $normalizedData = $group->buildNormalizedData($refl, Utils::toArray($object));
        return $normalizedData->modifyExistingObject($existingObject);
    }
}

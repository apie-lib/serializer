<?php
namespace Apie\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Lists\NormalizerList;
use Apie\Serializer\Normalizers\BooleanNormalizer;
use Apie\Serializer\Normalizers\EnumNormalizer;
use Apie\Serializer\Normalizers\FloatNormalizer;
use Apie\Serializer\Normalizers\IntegerNormalizer;
use Apie\Serializer\Normalizers\StringNormalizer;
use Apie\Serializer\Normalizers\ValueObjectNormalizer;
use ReflectionClass;
use ReflectionProperty;

class Serializer
{
    public function __construct(private NormalizerList $normalizers)
    {
    }

    public static function create(): self
    {
        return new self(new NormalizerList([
            new EnumNormalizer(),
            new ValueObjectNormalizer(),
            new StringNormalizer(),
            new IntegerNormalizer(),
            new FloatNormalizer(),
            new BooleanNormalizer(),
        ]));
    }

    public function normalize(mixed $object, ApieContext $apieContext): string|int|float|bool|ItemList|ItemHashmap|null
    {
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        foreach ($this->normalizers->iterateOverNormalizers() as $normalizer) {
            if ($normalizer->supportsNormalization($object, $serializerContext)) {
                return $normalizer->normalize($object, $serializerContext);
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
        $returnValue = [];
        foreach ($apieContext->getApplicableGetters(new ReflectionClass($object)) as $name => $getter) {
            if ($getter instanceof ReflectionProperty) {
                $returnValue[$name] = $serializerContext->normalizeChildElement($name, $getter->getValue($object));
                continue;
            }
            // todo run getters with extra arguments for context
            $returnValue[$name] = $serializerContext->normalizeChildElement($name, $getter->invoke($object));
        }
        return new ItemHashmap($returnValue);
    }

    public function denormalizeNewObject(string|int|float|bool|ItemList|ItemHashmap|array|null $object, string $desiredType, ApieContext $apieContext): mixed
    {
        if (is_array($object)) {
            $object = new ItemHashmap($object);
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
        $constructor = $refl->getConstructor();
        $arguments = [];
        if ($constructor) {
            $arguments = $serializerContext->denormalizeFromMethod($object, $constructor);
        }
        $createdObject = new $desiredType(...$arguments);
        return $this->denormalizeOnExistingObject($object, $createdObject, $apieContext);
    }

    public function denormalizeOnExistingObject(ItemHashmap $object, object $existingObject, ApieContext $apieContext): mixed
    {
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        foreach ($apieContext->getApplicableSetters(new ReflectionClass($existingObject)) as $name => $setter) {
            if (!isset($object[$name])) {
                continue;
            }
            if ($setter instanceof ReflectionProperty) {
                $setter->setValue($existingObject, $serializerContext->denormalizeFromTypehint($object[$name], $setter->getType()));
                continue;
            }
            // todo run setters with extra arguments for context
            //$returnValue[$name] = $serializerContext->normalizeChildElement($name, $setter->invoke($object));
        }
        return $existingObject;
    }
}

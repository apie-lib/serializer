<?php
namespace Apie\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Lists\NormalizerList;
use Apie\Serializer\Normalizers\BooleanNormalizer;
use Apie\Serializer\Normalizers\EnumNormalizer;
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

    public function denormalize(string|int|float|bool|ItemList|ItemHashmap|array|null $object, string $desiredType, ApieContext $apieContext): mixed
    {
        if (is_array($object)) {
            $object = new ItemList($object);
        }
        $serializerContext = new ApieSerializerContext($this, $apieContext);
        foreach ($this->normalizers->iterateOverDenormalizers() as $denormalizer) {
            if ($denormalizer->supportsDenormalization($object, $desiredType, $serializerContext)) {
                return $denormalizer->denormalize($object, $desiredType, $serializerContext);
            }
        }
        // TODO default behaviour
    }
}

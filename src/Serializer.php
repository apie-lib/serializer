<?php
namespace Apie\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Lists\NormalizerList;
use Apie\Serializer\Normalizers\EnumNormalizer;

class Serializer
{
    public function __construct(private NormalizerList $normalizers)
    {
    }

    public static function create(): self
    {
        return new self(new NormalizerList([new EnumNormalizer()]));
    }

    public function normalize(mixed $object, ApieContext $apieContext): string|int|float|bool|ItemList|ItemHashmap|null
    {
        $serializerContext = new ApieSerializerContext($apieContext);
        foreach ($this->normalizers->iterateOverNormalizers() as $normalizer) {
            if ($normalizer->supportsNormalization($object, $serializerContext)) {
                return $normalizer->normalize($object, $serializerContext);
            }
        }
        // TODO: default behaviour
    }

    public function denormalize(string|int|float|bool|ItemList|ItemHashmap|array|null $object, string $desiredType, ApieContext $apieContext): mixed
    {
        if (is_array($object)) {
            $object = new ItemList($object);
        }
        $serializerContext = new ApieSerializerContext($apieContext);
        foreach ($this->normalizers->iterateOverDenormalizers() as $denormalizer) {
            if ($denormalizer->supportsDenormalization($object, $desiredType, $serializerContext)) {
                return $denormalizer->denormalize($object, $desiredType, $serializerContext);
            }
        }
        // TODO default behaviour
    }
}

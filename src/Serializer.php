<?php
namespace Apie\Serializer;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Lists\DenormalizerList;
use Apie\Serializer\Lists\NormalizerList;

class Serializer
{
    public function __construct(private NormalizerList $normalizers, private DenormalizerList $denormalizerList)
    {
    }

    public function normalize(mixed $object, ApieContext $apieContext): string|int|float|bool|ItemList|ItemHashmap|null
    {
        $serializerContext = new ApieSerializerContext($apieContext);
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->isSupported($object, $serializerContext)) {
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
        foreach ($this->denormalizers as $denormalizer) {
            if ($denormalizer->isSupported($object, $desiredType, $serializerContext)) {
                return $denormalizer->normalize($object, $desiredType, $serializerContext);
            }
        }
        // TODO default behaviour
    }
}
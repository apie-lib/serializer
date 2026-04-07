<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\Serializer\Interfaces\NormalizeSelfInterface;

class SelfNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof NormalizeSelfInterface;
    }

    /**
     * @param NormalizeSelfInterface $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap
    {
        return $apieSerializerContext->normalizeAgain($object->normalize($apieSerializerContext));
    }
}

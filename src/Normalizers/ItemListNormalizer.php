<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;

class ItemListNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof ItemList;
    }

    /**
     * @param ItemList $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap
    {
        $list = [];
        foreach ($object as $key => $value) {
            $list[$key] = $apieSerializerContext->normalizeChildElement($key, $value);
        }

        return new ItemList($list);
    }
}

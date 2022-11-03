<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\Serializer\Lists\SerializedList;

class ItemListNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof ItemList && !($object instanceof SerializedList);
    }

    /**
     * @param ItemList $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): SerializedList
    {
        $list = [];
        foreach ($object as $key => $value) {
            $list[$key] = $apieSerializerContext->normalizeChildElement((string) $key, $value);
        }

        return new SerializedList($list);
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === ItemHashmap::class || $desiredType === ItemList::class;
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        if (is_a($object, $desiredType, true)) {
            return $object;
        }
        return new $desiredType(Utils::toArray($object));
    }
}

<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Lists\ItemSet;
use Apie\Core\Utils\HashmapUtils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\Serializer\Lists\SerializedHashmap;
use Apie\Serializer\Lists\SerializedList;

class ItemListNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        if ($object instanceof ItemList && !($object instanceof SerializedList)) {
            return true;
        }
        if ($object instanceof ItemSet) {
            return true;
        }

        return ($object instanceof ItemHashmap && !($object instanceof SerializedHashmap));
    }

    /**
     * @param ItemList|ItemHashmap|ItemSet $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): SerializedList|SerializedHashmap
    {
        $list = [];
        foreach ($object as $key => $value) {
            $childElement = $apieSerializerContext->normalizeChildElement((string) $key, $value);
            $list[$key] = $childElement;
        }

        return $object instanceof ItemHashmap ? new SerializedHashmap($list) : new SerializedList($list);
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return HashmapUtils::isHashmap($desiredType) || HashmapUtils::isList($desiredType) || HashmapUtils::isSet($desiredType);
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        if (is_a($object, $desiredType, true)) {
            return $object;
        }
        $list = [];
        foreach ($object as $key => $value) {
            $list[$key] = $apieSerializerContext->denormalizeChildElement((string) $key, $value, HashmapUtils::getArrayType($desiredType));
        }
        return new $desiredType($list);
    }
}

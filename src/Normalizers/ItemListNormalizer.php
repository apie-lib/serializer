<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Lists\ItemSet;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Utils\HashmapUtils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\Serializer\Lists\SerializedHashmap;
use Apie\Serializer\Lists\SerializedList;
use Exception;
use Psr\Http\Message\UploadedFileInterface;

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
        if ($object instanceof PermissionList) {
            $object = $object->toStringList();
        }
        $list = [];
        foreach ($object as $key => $value) {
            $childElement = $apieSerializerContext->normalizeChildElement((string) $key, $value);
            $list[$key] = $childElement;
        }

        return $object instanceof ItemHashmap ? new SerializedHashmap($list) : new SerializedList($list);
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === 'array' || HashmapUtils::isHashmap($desiredType) || HashmapUtils::isList($desiredType) || HashmapUtils::isSet($desiredType);
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        if (is_a($object, $desiredType, true)) {
            return $object;
        }
        if (!is_iterable($object)) {
            throw new InvalidTypeException($object, 'iterable');
        }
        $list = [];
        $validationErrors = [];
        $iterator = $object->getIterator();
        $arrayType = HashmapUtils::getArrayType($desiredType);
        $key = '';
        while ($iterator->valid()) {
            try {
                do {
                    $key = $iterator->key();
                    $value = $iterator->current();
                    $iterator->next();
                    $list[$key] = $apieSerializerContext->denormalizeChildElement(
                        (string) $key,
                        $value,
                        $arrayType
                    );
                } while ($iterator->valid());
            } catch (Exception $throwable) {
                $validationErrors[(string) $key] = $throwable;
            }
        }
        if (!empty($validationErrors)) {
            throw ValidationException::createFromArray($validationErrors);
        }
        return $desiredType === 'array' ? $list : new $desiredType($list);
    }
}

<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemHashmap;

/**
 * A hashmap of items that is already serialized and does not need to be serialized again.
 *
 * @see ItemListNormalizer
 */
final class SerializedHashmap extends ItemHashmap
{
    protected bool $mutable = false;

    /**
     * @return mixed[]|string|int|float|bool
     */
    public function offsetGet(mixed $offset): array|string|int|float|bool|null|SerializedList|SerializedHashmap
    {
        return parent::offsetGet($offset);
    }
}

<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;

/**
 * A list of items that is already serialized and does not need to be serialized again.
 *
 * @see ItemListNormalizer
 */
final class SerializedList extends ItemList
{
    protected bool $mutable = false;

    /**
     * @return mixed[]|string|int|float|bool
     */
    public function offsetGet(mixed $offset): array|string|int|float|bool|SerializedList|ItemHashmap
    {
        return parent::offsetGet($offset);
    }
}

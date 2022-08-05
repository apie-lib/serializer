<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemList;
use Apie\Serializer\Normalizers\ItemListNormalizer;

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
    public function offsetGet(mixed $offset): array|string|int|float|bool
    {
        return parent::offsetGet($offset);
    }
}
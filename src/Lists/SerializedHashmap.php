<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;

/**
 * A hashmap of items that is already serialized and does not need to be serialized again.
 *
 * @see ItemListNormalizer
 */
final class SerializedHashmap extends ItemHashmap
{
    protected bool $mutable = false;

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($value instanceof ItemList && !($value instanceof SerializedList)) {
            $value = new SerializedList($value->toArray());
        } elseif ($value instanceof ItemHashmap && !($value instanceof SerializedHashmap)) {
            $value = new SerializedHashmap($value->toArray());
        } elseif (is_array($value)) {
            $value = new SerializedHashmap($value);
        }
        parent::offsetSet($offset, $value);
    }

    /**
     * @return mixed[]|string|int|float|bool
     */
    public function offsetGet(mixed $offset): array|string|int|float|bool|null|SerializedList|SerializedHashmap
    {
        return parent::offsetGet($offset);
    }
}

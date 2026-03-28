<?php
namespace Apie\Serializer\PropertySerializer;

use Apie\Core\Lists\ItemHashmap;

class TypehintMap extends ItemHashmap
{
    public function offsetGet(mixed $offset): Typehint
    {
        return parent::offsetGet($offset);
    }
}

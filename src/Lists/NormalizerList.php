<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemList;
use Apie\Serializer\Interfaces\NormalizerInterface;

class NormalizerList extends ItemList
{
    public function offsetGet(mixed $offset): NormalizerInterface
    {
        return parent::offsetGet($offset);
    }
}
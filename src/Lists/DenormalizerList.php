<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemList;
use Apie\Serializer\Interfaces\DenormalizerInterface;

class DenormalizerList extends ItemList
{
    public function offsetGet(mixed $offset): DenormalizerInterface
    {
        return parent::offsetGet($offset);
    }
}
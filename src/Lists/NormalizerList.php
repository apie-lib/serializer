<?php
namespace Apie\Serializer\Lists;

use Apie\Core\Lists\ItemList;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;

class NormalizerList extends ItemList
{
    public function offsetGet(mixed $offset): NormalizerInterface|DenormalizerInterface
    {
        return parent::offsetGet($offset);
    }

    /**
     * @return iterable<NormalizerInterface>
     */
    public function iterateOverNormalizers(): iterable
    {
        foreach ($this as $item) {
            if ($item instanceof NormalizerInterface) {
                yield $item;
            }
        }
    }

    /**
     * @return iterable<DenormalizerInterface>
     */
    public function iterateOverDenormalizers(): iterable
    {
        foreach ($this as $item) {
            if ($item instanceof DenormalizerInterface) {
                yield $item;
            }
        }
    }
}

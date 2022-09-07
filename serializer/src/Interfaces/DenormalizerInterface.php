<?php
namespace Apie\Serializer\Interfaces;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;

interface DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool;
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed;
}

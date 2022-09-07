<?php
namespace Apie\Serializer\Interfaces;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;

interface NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool;
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap;
}

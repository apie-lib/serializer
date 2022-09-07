<?php
namespace Apie\Serializer\Interfaces;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;

interface EncoderInterface
{
    public function encode(string|int|float|bool|array|null|ItemHashmap|ItemList $input): string;
}

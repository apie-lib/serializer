<?php
namespace Apie\Serializer\Encoders;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Interfaces\EncoderInterface;

class JsonEncoder implements EncoderInterface
{
    public function encode(string|int|float|bool|array|null|ItemHashmap|ItemList $input): string
    {
        return json_encode($input, JSON_UNESCAPED_SLASHES);
    }
}

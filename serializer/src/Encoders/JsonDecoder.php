<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;

class JsonDecoder implements DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null
    {
        return json_decode($input, true);
    }
}

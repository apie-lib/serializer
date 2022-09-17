<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;

class FormSubmitDecoder implements DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null
    {
        parse_str($input, $formContents);
        return $formContents['form'] ?? [];
    }
}
<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;

class FormSubmitDecoder implements DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null
    {
        $formContents = urldecode($input);
        return $formContents['form'] ?? [];
    }
}
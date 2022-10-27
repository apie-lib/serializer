<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;

class FormSubmitDecoder implements DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null
    {
        parse_str($input, $formContents);
        $data = $formContents['form'] ?? [];
        $data['_csrf'] = $formContents['_csrf'] ?? 'no csrf';
        // TODO internal data to add missing fields that are not compatible

        return $data;
    }
}

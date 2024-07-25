<?php
namespace Apie\Serializer\Encoders;

use Apie\Serializer\Interfaces\DecoderInterface;

class JsonDecoder implements DecoderInterface
{
    public function withParsedBody(null|array|object $parsedBody): DecoderInterface
    {
        return $this;
    }
    
    public function withOptions(string $options): DecoderInterface
    {
        return $this;
    }

    public function decode(string $input): string|int|float|bool|array|null
    {
        return json_decode($input, true);
    }

    public function requiresCsrf(): bool
    {
        return false;
    }
}

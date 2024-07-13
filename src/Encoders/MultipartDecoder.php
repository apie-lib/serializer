<?php
namespace Apie\Serializer\Encoders;

use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Serializer\Interfaces\DecoderInterface;

class MultipartDecoder implements DecoderInterface
{
    private array|object|null $parsedBody = null;

    public function withParsedBody(null|array|object $parsedBody): DecoderInterface
    {
        $res = clone $this;
        $res->parsedBody = $parsedBody === null ? null : (array) $parsedBody;
        return $res;
    }
    
    public function withOptions(string $options): DecoderInterface
    {
        return $this;
    }

    public function decode(string $input): string|int|float|bool|array|null
    {
        if (!is_array($this->parsedBody) || !array_key_exists('form', $this->parsedBody)) {
            throw new IndexNotFoundException('form');
        }
        return json_decode($this->parsedBody['form'], true);
    }
}

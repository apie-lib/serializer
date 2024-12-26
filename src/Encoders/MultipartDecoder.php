<?php
namespace Apie\Serializer\Encoders;

use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Interfaces\DecoderInterface;

class MultipartDecoder implements DecoderInterface
{
    private array|null $parsedBody = null;

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
            $form = [];
        } else {
            $form = Utils::toArray($this->parsedBody)['form'] ?? [];
        }
        if (is_string($form)) {
            $data = json_decode($form, true);
        } else {
            $data = (new FormSubmitDecoder())->withParsedBody($this->parsedBody)->decode(http_build_query($this->parsedBody));
        }
        return $data;
    }

    public function requiresCsrf(): bool
    {
        return true;
    }
}

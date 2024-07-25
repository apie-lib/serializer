<?php
namespace Apie\Serializer\Interfaces;

interface DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null;
    public function withOptions(string $options): DecoderInterface;
    public function withParsedBody(null|array|object $parsedBody): DecoderInterface;
    public function requiresCsrf(): bool;
}

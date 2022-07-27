<?php
namespace Apie\Serializer\Interfaces;

interface DecoderInterface
{
    public function decode(string $input): string|int|float|bool|array|null;
}

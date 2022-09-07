<?php
namespace Apie\Serializer;

use Apie\Core\Lists\ItemHashmap;
use Apie\Serializer\Encoders\JsonDecoder;
use Apie\Serializer\Interfaces\DecoderInterface;

final class DecoderHashmap extends ItemHashmap
{
    protected bool $mutable = false;

    public function offsetGet(mixed $offset): DecoderInterface
    {
        return parent::offsetGet($offset);
    }

    public static function create(): self
    {
        return new self(['application/json' => new JsonDecoder()]);
    }
}

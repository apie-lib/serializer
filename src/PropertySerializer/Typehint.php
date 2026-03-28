<?php
namespace Apie\Serializer\PropertySerializer;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Lists\ItemSet;

final class Typehint
{
    public function __construct(
        public readonly TypeDefinition $type,
        public readonly string|int|float|bool|null|TypehintMap $value
    ) {
    }

    public static function createFromPrimitive(string|int|float|bool|null $value): self
    {
        return new self(TypeDefinition::from(get_debug_type($value)), $value);
    }

    public static function createFromObject(object $object, TypehintMap $properties): self
    {
        if ($object instanceof ItemHashmap) {
            return new self(TypeDefinition::Map, $properties);
        }
        if ($object instanceof ItemList || $object instanceof ItemSet) {
            return new self(TypeDefinition::Array, $properties);
        }
        return new self(TypeDefinition::ObjectReference, spl_object_hash($object));
    }
}

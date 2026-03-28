<?php
namespace Apie\Serializer\PropertySerializer;

use JsonSerializable;
use WeakMap;

final class SerializedProperties implements JsonSerializable
{
    private ?Typehint $root = null;

    private array $registeredObjects = [];

    /** @var WeakMap<object, mixed> */
    private WeakMap $registeredObjectsMap;

    public function __construct()
    {
        $this->registeredObjectsMap = new WeakMap();
    }

    public static function createFromPrimitive(string|int|float|bool|null $value): self
    {
        $instance = new self();
        $instance->root = Typehint::createFromPrimitive($value);
        return $instance;
    }

    public static function createFromMap(TypehintMap $properties): self
    {
        $instance = new self();
        $instance->root = new Typehint(TypeDefinition::Map, $properties);
        return $instance;
    }

    public static function createFromList(TypehintMap $properties): self
    {
        $instance = new self();
        $instance->root = new Typehint(TypeDefinition::Array, $properties);
        return $instance;
    }

    public function isRegistered(object $object): bool
    {
        return isset($this->registeredObjectsMap[$object]);
    }
    public function getRoot(): Typehint
    {
        if (null === $this->root) {
            throw new \LogicException('Root is not set');
        }
        return $this->root;
    }

    public function withRegisteredObject(object $object, TypehintMap $result): self
    {
        if (isset($this->registeredObjectsMap[$object])) {
            return $this;
        }
        $clone = clone $this;
        if (null === $clone->root) {
            $clone->root = Typehint::createFromObject($object, $result);
        }
        $clone->registeredObjectsMap[$object] = $result;
        $clone->registeredObjects[spl_object_hash($object)] = $object;
        return $clone;
    }

    public function jsonSerialize(): mixed
    {
        $objects = [
            'objects' => [],
            'root' => $this->root,
        ];
        foreach ($this->registeredObjects as $hash => $object) {
            $objects['objects'][$hash] = $this->registeredObjectsMap[$object];
        }

        return $objects;
    }
}

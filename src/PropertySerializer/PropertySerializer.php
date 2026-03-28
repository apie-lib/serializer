<?php
namespace Apie\Serializer\PropertySerializer;

use ReflectionClass;
use ReflectionProperty;
use stdClass;

class PropertySerializer
{
    public function toJson(mixed $object): SerializedProperties
    {
        return match(get_debug_type($object)) {
            'null', 'string', 'int', 'float', 'bool' => SerializedProperties::createFromPrimitive($object),
            'array' => $this->toJsonArray($object),
            stdClass::class => $this->toJsonStdclass($object),
            default => $this->toJsonObject($object),
        };
    }

    private function toJsonArray(array $object): SerializedProperties
    {
        $isList = true;
        $count = 0;
        $returnValue = [];
        foreach (array_keys($object) as $key) {
            if ($key === $count) {
                $count++;
            } else {
                $isList = false;
                break;
            }
            $returnValue[$key] = $this->toJson($object[$key])->getRoot();
        }
        return $isList
            ? SerializedProperties::createFromList(new TypehintMap($returnValue))
            : SerializedProperties::createFromMap(new TypehintMap($returnValue));
    }

    private function toJsonStdclass(stdClass $object): SerializedProperties
    {
        $returnValue = [];
        // @phpstan-ignore foreach.nonIterable
        foreach ($object as $key => $value) {
            $returnValue[$key] = $this->toJson($value)->getRoot();
        }
        return SerializedProperties::createFromMap(new TypehintMap($returnValue));
    }

    /**
     * @param ReflectionClass<object> $reflectionClass
     * @return iterable<string, ReflectionProperty>
     */
    private function iterate(ReflectionClass $reflectionClass, int $filter): iterable
    {
        foreach ($reflectionClass->getProperties($filter) as $property) {
            $name = $property->isPrivate() ? $property->getDeclaringClass()->getName() . '::' . $property->getName() : $property->getName();
            yield $name => $property;
        }
        $parent = $reflectionClass->getParentClass();
        if ($parent && $filter !== ReflectionProperty::IS_PRIVATE) {
            yield from $this->iterate($parent, ReflectionProperty::IS_PRIVATE);
        }
    }

    private function toJsonObject(object $object): SerializedProperties
    {
        $returnValue = [];
        foreach ($this->iterate(new ReflectionClass($object), ReflectionProperty::IS_PUBLIC|ReflectionProperty::IS_PROTECTED) as $key => $property) {
            if ($property->isInitialized($object)) {
                $returnValue[$key] = $this->toJson($property->getValue($object))->getRoot();
            }
        }
        return SerializedProperties::createFromMap(new TypehintMap($returnValue));
    }
}

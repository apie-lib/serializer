<?php
namespace Apie\Serializer\Context;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Serializer;
use Exception;
use ReflectionMethod;
use ReflectionUnionType;
use RuntimeException;

final class ApieSerializerContext
{
    public function __construct(private Serializer $serializer, private ApieContext $apieContext)
    {
    }

    public function denormalizeFromMethod(mixed $input, ReflectionMethod $method): array
    {
        if (! $input instanceof ItemHashmap) {
            $input = $this->serializer->denormalizeNewObject($input, ItemHashmap::class, $this->apieContext);
        }
        $result = [];
        foreach ($method->getParameters() as $parameter) {
            $key = $parameter->getName();
            $type = $parameter->getType();
            $defaultValue = $parameter->isOptional() ? $parameter->getDefaultValue() : null;
            if ($type === null || ((string) $type) === 'mixed') {
                $result[] = $input[$key] ?? $defaultValue;
                continue;
            }
            if ($type instanceof ReflectionUnionType) {
                $lastException = new RuntimeException('Unknown error');
                foreach ($type->getTypes() as $type) {
                    try {
                        $outcome = $this->denormalizeChildElement($key, $input[$key] ?? $defaultValue, $type->getName());
                        $result[] = $outcome;
                        continue(2);
                    } catch (Exception $exception) {
                        $lastException = $exception;
                    }
                }
                throw $lastException;
            }
            $outcome = $this->denormalizeChildElement($key, $input[$key] ?? $defaultValue, $type->getName());
            $result[] = $outcome;
        }
        return $result;
    }

    public function denormalizeChildElement(string $key, mixed $input, string $desiredType): mixed
    {
        $newContext = $this->createChildContext($key);
        return $this->serializer->denormalizeNewObject($input, $desiredType, $newContext);
    }

    public function normalizeAgain(mixed $object): string|int|float|bool|ItemList|ItemHashmap|null
    {
        return $this->serializer->normalize($object, $this->apieContext);
    }

    public function normalizeChildElement(string $key, mixed $object): string|int|float|bool|ItemList|ItemHashmap|null
    {
        $newContext = $this->createChildContext($key);
        return $this->serializer->normalize($object, $newContext);
    }

    public function createChildContext(string $key): ApieContext
    {
        $hierarchy = [];
        if ($this->apieContext->hasContext('hierarchy')) {
            $hierarchy = $this->apieContext->getContext('hierarchy');
        }
        $hierarchy[] = $key;
        return $this->apieContext->withContext('hierarchy', $hierarchy);
    }

    public function getContext(): ApieContext
    {
        return $this->apieContext;
    }
}

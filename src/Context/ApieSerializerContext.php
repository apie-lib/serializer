<?php
namespace Apie\Serializer\Context;

use Apie\Core\Attributes\Context;
use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Metadata\Concerns\UseContextKey;
use Apie\Serializer\Serializer;
use Exception;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use RuntimeException;

final class ApieSerializerContext
{
    use UseContextKey;

    public function __construct(private Serializer $serializer, private ApieContext $apieContext)
    {
    }

    public function denormalizeFromTypehint(mixed $input, ReflectionType|null $typehint): mixed
    {
        if ($input === null && (!$typehint || $typehint->allowsNull())) {
            return null;
        }
        if ($typehint instanceof ReflectionIntersectionType) {
            throw new InvalidTypeException($typehint, 'ReflectionNamedType|ReflectionUnionType|null');
        }
        if ($typehint instanceof ReflectionUnionType) {
            $lastException = new RuntimeException('Unknown error');
            foreach ($typehint->getTypes() as $type) {
                try {
                    return $this->serializer->denormalizeNewObject($input, $type->getName(), $this->apieContext);
                } catch (Exception $exception) {
                    $lastException = $exception;
                }
            }
            throw $lastException;
        }
        if ($typehint instanceof ReflectionNamedType) {
            return $this->serializer->denormalizeNewObject($input, $typehint->getName(), $this->apieContext);
        }
        return $this->serializer->denormalizeNewObject($input, 'mixed', $this->apieContext);
    }

    public function denormalizeFromParameter(ItemHashmap $input, ReflectionParameter $parameter): mixed
    {
        $key = $parameter->getName();
        $type = $parameter->getType();
        if ($parameter->getAttributes(Context::class)) {
            $contextKey = $this->getContextKey($this->apieContext, $parameter, false);
            return $this->apieContext->getContext($contextKey);
        }
        if (!$parameter->isOptional() && !isset($input[$key])) {
            throw new IndexNotFoundException($key);
        }
        $defaultValue = $parameter->isOptional() ? $parameter->getDefaultValue() : null;
        if ($type === null || ((string) $type) === 'mixed' || !isset($input[$key])) {
            return $input[$key] ?? $defaultValue;
        }
        $newContext = new self($this->serializer, $this->createChildContext($key));
        return $newContext->denormalizeFromTypehint($input[$key], $type);
    }

    public function denormalizeFromMethod(mixed $input, ReflectionMethod $method): array
    {
        if (! ($input instanceof ItemHashmap)) {
            $input = $this->serializer->denormalizeNewObject($input, ItemHashmap::class, $this->apieContext);
        }
        $result = [];
        // TODO: validation errors
        foreach ($method->getParameters() as $parameter) {
            $result[] = $this->denormalizeFromParameter($input, $parameter);
        }
        return $result;
    }

    public function denormalizeChildElement(string $key, mixed $input, string $desiredType): mixed
    {
        $newContext = $this->createChildContext($key);
        return $this->serializer->denormalizeNewObject($input, $desiredType, $newContext);
    }

    public function normalizeAgain(mixed $object, bool $forceDefaultNormalization = false): string|int|float|bool|ItemList|ItemHashmap|null
    {
        return $this->serializer->normalize($object, $this->apieContext, $forceDefaultNormalization);
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

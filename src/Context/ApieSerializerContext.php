<?php
namespace Apie\Serializer\Context;

use Apie\Common\ContextConstants;
use Apie\Core\Attributes\Context;
use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Metadata\Concerns\UseContextKey;
use Apie\Core\Utils\ConverterUtils;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Serializer;
use Apie\TypeConverter\Exceptions\CanNotConvertObjectToUnionException;
use Exception;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

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
            $exceptions = [];
            foreach ($typehint->getTypes() as $type) {
                try {
                    return $this->serializer->denormalizeNewObject($input, $type->getName(), $this->apieContext);
                } catch (Exception $exception) {
                    $exceptions[$type->getName()] = $exception;
                }
            }
            throw new CanNotConvertObjectToUnionException($input, $exceptions, $typehint);
        }
        if ($typehint instanceof ReflectionNamedType) {
            // edge case, should probably work differently then this
            if ($input === '' && $typehint->allowsNull() && $this->apieContext->hasContext(ContextConstants::CMS)) {
                try {
                    $this->serializer->denormalizeNewObject($input, $typehint->getName(), $this->apieContext);
                } catch (InvalidStringForValueObjectException) {
                    return null;
                }
            }
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
            if ($type) {
                return ConverterUtils::dynamicCast($this->apieContext->getContext($contextKey), $type);
            }
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
        $validationErrors = [];
        // this construction is for performance reasons as it maintains only one try catch context.
        $todo = $method->getParameters();
        while (!empty($todo)) {
            try {
                while (!empty($todo)) {
                    $parameter = array_shift($todo);
                    $result[] = $this->denormalizeFromParameter($input, $parameter);
                }
            } catch (Exception $error) {
                assert(isset($parameter));
                $validationErrors[$parameter->name] = $error;
            }
        }
        if (!empty($validationErrors)) {
            throw ValidationException::createFromArray($validationErrors);
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

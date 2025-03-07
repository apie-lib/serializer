<?php
namespace Apie\Serializer\Context;

use Apie\Core\Attributes\Context;
use Apie\Core\Context\ApieContext;
use Apie\Core\ContextConstants;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Exceptions\InvalidTypeException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Metadata\Concerns\UseContextKey;
use Apie\Core\TypeUtils;
use Apie\Core\Utils\ConverterUtils;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\FieldFilters\FieldFilterInterface;
use Apie\Serializer\FieldFilters\NoFiltering;
use Apie\Serializer\Relations\EmbedRelationInterface;
use Apie\Serializer\Relations\NoRelationEmbedded;
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

    private ?ApieSerializerContext $parentState = null;

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
            if ($input === '' && $typehint->allowsNull() && !TypeUtils::allowEmptyString($typehint) && $this->apieContext->hasContext(ContextConstants::CMS)) {
                return null;
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
            $contextValue = $this->apieContext->getContext(
                $contextKey,
                !$parameter->isDefaultValueAvailable()
            ) ?? $parameter->getDefaultValue();
            if ($type) {
                return ConverterUtils::dynamicCast($contextValue, $type);
            }
            return $contextValue;
        }
        if (!$parameter->isOptional() && !isset($input[$key])) {
            throw new IndexNotFoundException($key);
        }
        $defaultValue = $parameter->isOptional() ? $parameter->getDefaultValue() : null;
        if ($type === null || ((string) $type) === 'mixed' || !isset($input[$key])) {
            return $input[$key] ?? $defaultValue;
        }
        $newContext = $this->visit($key);
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

    private function createChildContext(string $key): ApieContext
    {
        $context = $this->apieContext;
        $hierarchy = [];
        if ($context->hasContext('hierarchy')) {
            $hierarchy = $context->getContext('hierarchy');
        }
        $hierarchy[] = $key;
        $fieldFilter = $context->getContext(FieldFilterInterface::class, false) ? : new NoFiltering();
        $context = $context->withContext(FieldFilterInterface::class, $fieldFilter->followField($key));

        $relationFilter = $context->getContext(EmbedRelationInterface::class, false) ? : new NoRelationEmbedded();
        $context = $context->withContext(EmbedRelationInterface::class, $relationFilter->followField($key));

        return $context->withContext('hierarchy', $hierarchy);
    }

    public function visit(string $key): self
    {
        $childContext = $this->createChildContext($key);
        $res = new ApieSerializerContext($this->serializer, $childContext);
        $res->parentState = $this;
        return $res;
    }

    public function getParentState(): ?self
    {
        return $this->parentState;
    }

    public function getContext(): ApieContext
    {
        return $this->apieContext;
    }
}

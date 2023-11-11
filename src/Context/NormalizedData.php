<?php
namespace Apie\Serializer\Context;

use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Serializer\Exceptions\ValidationException;
use Exception;
use ReflectionClass;

/**
 * @template T of object
 */
class NormalizedData
{
    /**
     * @param ReflectionClass<T> $class
     * @param array<string, NormalizedValue> $normalizedValues
     * @param array<string, Exception> $validationErrors
     */
    public function __construct(
        private readonly ReflectionClass $class,
        private readonly ApieContext $apieContext,
        private readonly array $normalizedValues,
        private readonly array $validationErrors
    ) {
    }

    private function validationCheck(): void
    {
        if (!empty($this->validationErrors)) {
            throw ValidationException::createFromArray($this->validationErrors);
        }
    }

    /**
     * @return T
     */
    public function createNewObject(): object
    {
        $this->validationCheck();
        $constructor = $this->class->getConstructor();
        $constructorArguments = [];
        if ($constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                if (array_key_exists($parameter->name, $this->normalizedValues)) {
                    $constructorArguments[] = $this->normalizedValues[$parameter->name]->getNormalizedValue();
                } else {
                    if (!$parameter->isDefaultValueAvailable()) {
                        throw new IndexNotFoundException($parameter->name);
                    }
                    $constructorArguments[] = $parameter->getDefaultValue();
                }
            }
        }
        $object = $this->class->newInstanceArgs($constructorArguments);

        return $this->callSetters($object);
    }

    /**
     * @param T $object
     * @return T
     */
    private function callSetters(object $object): object
    {
        foreach ($this->normalizedValues as $normalizedValue) {
            $value = $normalizedValue->getNormalizedValue();
            $fieldMetadata = $normalizedValue->getFieldMetadata();
            $fieldMetadata->setValue(
                $object,
                $value,
                $this->apieContext
            );
        }
        return $object;
    }

    /**
     * @param T $object
     * @return T
     */
    public function modifyExistingObject(object $object): object
    {
        $this->validationCheck();

        return $this->callSetters($object);
    }
}

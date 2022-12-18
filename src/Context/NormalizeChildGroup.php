<?php
namespace Apie\Serializer\Context;

use Apie\Core\Context\ApieContext;
use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Metadata\Fields\FallbackFieldInterface;
use Apie\Core\Metadata\Fields\FieldInterface;
use Apie\Core\Metadata\MetadataInterface;
use Apie\Core\Metadata\SetterInterface;
use Apie\Serializer\Exceptions\ThisIsNotAFieldException;
use Exception;
use ReflectionClass;

class NormalizeChildGroup
{
    private ApieContext $apieContext;

    public function __construct(
        private readonly ApieSerializerContext $serializerContext,
        private readonly MetadataInterface $metadata
    ) {
        $this->apieContext = $serializerContext->getContext();
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $class
     * @return NormalizedData<T>
     */
    public function buildNormalizedData(ReflectionClass $class, array $input): NormalizedData
    {
        $built = [];
        $validationErrors = [];
        $todoList = [];
        foreach ($this->metadata->getHashmap()->filterOnContext($this->apieContext, setters: true) as $fieldName => $fieldMetadata) {
            $todoList[] = [$fieldName, $fieldMetadata];
        }
        // this construction is for performance reasons as it maintains only one try catch context.
        while (!empty($todoList)) {
            /** @var FieldInterface&SetterInterface $fieldMetadata */
            list($fieldName, $fieldMetadata) = array_pop($todoList);
            try {
                if (!isset($input[$fieldName])) {
                    if ($fieldMetadata->isRequired()) {
                        $validationErrors[$fieldName] = new IndexNotFoundException($fieldName);
                    }
                    if ($fieldMetadata instanceof FallbackFieldInterface) {
                        $built[$fieldName] = $fieldMetadata->getMissingValue();
                    }
                    continue;
                }
                if (!$fieldMetadata->isField()) {
                    throw new ThisIsNotAFieldException($fieldName);
                }
                $built[$fieldName] = new NormalizedValue(
                    $this->serializerContext->denormalizeFromTypehint(
                        $input[$fieldName],
                        $fieldMetadata->getTypehint()
                    ),
                    $fieldMetadata
                );
            } catch (Exception $error) {
                $validationErrors[$fieldName] = $error;
            }
        }

        return new NormalizedData(
            $class,
            $this->apieContext,
            $built,
            $validationErrors
        );
    }
}

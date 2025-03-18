<?php
namespace Apie\Serializer\Context;

use Apie\Core\Metadata\Fields\FieldInterface;
use Apie\Core\Metadata\SetterInterface;

class NormalizedValue
{
    public function __construct(
        private readonly mixed $normalizedValue,
        private readonly FieldInterface&SetterInterface $fieldMetadata
    ) {
    }

    public function getFieldMetadata(): FieldInterface&SetterInterface
    {
        return $this->fieldMetadata;
    }

    public function getNormalizedValue(): mixed
    {
        return $this->normalizedValue;
    }
}

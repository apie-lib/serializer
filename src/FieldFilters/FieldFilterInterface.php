<?php
namespace Apie\Serializer\FieldFilters;

interface FieldFilterInterface
{
    public function isFiltered(string $fieldName): bool;

    public function followField(string $fieldName): FieldFilterInterface;
}

<?php
namespace Apie\Serializer\FieldFilters;

final class NoFiltering implements FieldFilterInterface
{
    public function isFiltered(string $fieldName): bool
    {
        return true;
    }

    public function followField(string $fieldName): FieldFilterInterface
    {
        return $this;
    }
}

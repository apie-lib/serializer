<?php
namespace Apie\Serializer\FieldFilters;

use Apie\Core\Lists\StringList;

final class FilterFromArray implements FieldFilterInterface
{
    private function __construct(private array $fieldMap)
    {
    }

    public static function createFromMixed(mixed $fieldNames): FieldFilterInterface
    {
        if (is_string($fieldNames)) {
            return self::createFromArray(
                new StringList(explode(',', $fieldNames))
            );
        }

        if (is_array($fieldNames)) {
            return self::createFromArray(
                new StringList($fieldNames)
            );
        }

        return new NoFiltering();
    }

    public static function createFromArray(StringList $fieldNames): FieldFilterInterface
    {
        if ($fieldNames->count() === 0) {
            return new NoFiltering();
        }
        $map = [];
        foreach ($fieldNames as $fieldName) {
            if (strpos($fieldName, '.') === false) {
                $map[$fieldName] = [];
            } else {
                $prefix = strstr($fieldName, '.', true);
                $map[$prefix] ??= [];
                $map[$prefix][] = substr(strstr($fieldName, '.'), 1);
            }
        }
        return new self($map);
    }

    public function isFiltered(string $fieldName): bool
    {
        return isset($this->fieldMap[$fieldName]) || isset($this->fieldMap['*']);
    }

    public function followField(string $fieldName): FieldFilterInterface
    {
        $list = [];
        if (isset($this->fieldMap[$fieldName])) {
            $list[] = self::createFromArray(new StringList($this->fieldMap[$fieldName]));
        }
        if (isset($this->fieldMap['*'])) {
            $list[] = self::createFromArray(new StringList($this->fieldMap['*']));
        }
        $list = array_filter(
            $list,
            function (FieldFilterInterface $filter) {
                return !($filter instanceof NoFiltering);
            }
        );
        if (empty($list)) {
            return new NoFiltering();
        }
        if (count($list) === 1) {
            return reset($list);
        }
        return new ChainedFilter(...$list);
    }
}

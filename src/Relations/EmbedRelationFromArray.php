<?php
namespace Apie\Serializer\Relations;

use Apie\Core\Lists\StringList;
use Apie\Core\Lists\StringSet;

class EmbedRelationFromArray implements EmbedRelationInterface
{
    private function __construct(private array $fieldMap, private bool $embedded, private array $embeddedMap)
    {
    }

    public static function createFromMixed(mixed $fieldNames): EmbedRelationInterface
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

        return new NoRelationEmbedded();
    }

    public static function createFromArray(StringList|StringSet $fieldNames, bool $embedded = false): EmbedRelationInterface
    {
        if ($fieldNames->count() === 0) {
            return new NoRelationEmbedded();
        }
        $map = [];
        $embeddedMap = [];
        foreach ($fieldNames as $fieldName) {
            if (strpos($fieldName, '.') === false) {
                $map[$fieldName] = [];
                $embeddedMap[$fieldName] = true;
            } else {
                $prefix = strstr($fieldName, '.', true);
                $map[$prefix] ??= [];
                $map[$prefix][] = substr(strstr($fieldName, '.'), 1);
            }
        }
        return new self($map, $embedded, $embeddedMap);
    }

    public function hasEmbeddedRelation(): bool
    {
        return $this->embedded;
    }

    public function followField(string $fieldName): EmbedRelationInterface
    {
        if ($fieldName === 'id') {
            return new NoRelationEmbedded();
        }
        $list = [];
        if (isset($this->fieldMap[$fieldName])) {
            if (count($this->fieldMap[$fieldName]) === 0) {
                return new HasRelationEmbedded();
            }
            $list[] = self::createFromArray(new StringList($this->fieldMap[$fieldName]), $this->embeddedMap[$fieldName] ?? false);
        }
        if (isset($this->fieldMap['*'])) {
            if (count($this->fieldMap['*']) === 0) {
                return new HasRelationEmbedded();
            }
            $list[] = self::createFromArray(new StringList($this->fieldMap['*']), $this->embeddedMap['*'] ?? false);
        }
        $list = array_filter(
            $list,
            function (EmbedRelationInterface $filter) {
                return !($filter instanceof NoRelationEmbedded);
            }
        );
        if (empty($list)) {
            return new NoRelationEmbedded();
        }
        if (count($list) === 1) {
            return reset($list);
        }
        return new ChainedRelation(...$list);
    }
}
<?php
namespace Apie\Serializer\Relations;

final class ChainedRelation implements EmbedRelationInterface
{
    /** @var array<int, EmbedRelationInterface> */
    private array $filters;

    public function __construct(EmbedRelationInterface... $filters)
    {
        $this->filters = $filters;
    }
    public function hasEmbeddedRelation(): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->hasEmbeddedRelation()) {
                return true;
            }
        }

        return false;
    }

    public function followField(string $fieldName): EmbedRelationInterface
    {
        if ($fieldName === 'id') {
            return new NoRelationEmbedded();
        }
        $list = [];
        foreach ($this->filters as $filter) {
            $list[] = $filter->followField($fieldName);
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

<?php
namespace Apie\Serializer\FieldFilters;

final class ChainedFilter implements FieldFilterInterface
{
    /** @var array<int, FieldFilterInterface> */
    private array $filters;

    public function __construct(FieldFilterInterface... $filters)
    {
        $this->filters = $filters;
    }
    public function isFiltered(string $fieldName): bool
    {
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($fieldName)) {
                return true;
            }
        }

        return false;
    }

    public function followField(string $fieldName): FieldFilterInterface
    {
        $list = [];
        foreach ($this->filters as $filter) {
            if ($filter->isFiltered($fieldName)) {
                $list[] = $this->followField($fieldName);
            }
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

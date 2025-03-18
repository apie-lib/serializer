<?php
namespace Apie\Serializer\Relations;

final class NoRelationEmbedded implements EmbedRelationInterface
{
    public function hasEmbeddedRelation(): bool
    {
        return false;
    }
    public function followField(string $fieldName): EmbedRelationInterface
    {
        return $this;
    }
}
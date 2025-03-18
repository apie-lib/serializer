<?php
namespace Apie\Serializer\Relations;

final class HasRelationEmbedded implements EmbedRelationInterface
{
    public function hasEmbeddedRelation(): bool
    {
        return true;
    }
    public function followField(string $fieldName): EmbedRelationInterface
    {
        return new NoRelationEmbedded();
    }
}
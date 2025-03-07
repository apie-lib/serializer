<?php
namespace Apie\Serializer\Relations;

interface EmbedRelationInterface
{
    public function hasEmbeddedRelation(): bool;
    public function followField(string $fieldName): EmbedRelationInterface;
}
<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextHashmap;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Exceptions\EntityNotFoundException;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\Serializer\Relations\EmbedRelationInterface;
use ReflectionClass;

class RelationNormalizer implements NormalizerInterface
{
    /**
     * @param ReflectionClass<object> $resourceClass
     */
    private function findBoundedContext(
        ApieSerializerContext $apieSerializerContext,
        ReflectionClass $resourceClass
    ): ?BoundedContextId {
        $hashmap = $apieSerializerContext->getContext()->getContext(BoundedContextHashmap::class, false);
        if ($hashmap instanceof BoundedContextHashmap) {
            return $hashmap->getBoundedContextFromClassName(
                $resourceClass,
                $apieSerializerContext->getContext()->getContext(BoundedContextId::class, false)
            )?->getId();
        }
        $boundedContext = $apieSerializerContext->getContext()->getContext(BoundedContext::class, false);
        if ($boundedContext instanceof BoundedContext && $boundedContext->contains($resourceClass)) {
            return $boundedContext->getId();
        }

        return null;

    }

    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        if (!$apieSerializerContext->getContext()->hasContext(ApieDatalayer::class)) {
            return false;
        }
        if (!$apieSerializerContext->getContext()->getContext(EmbedRelationInterface::class, false)?->hasEmbeddedRelation()) {
            return false;
        }
        if ($object instanceof IdentifierInterface) {
            $resourceClass = $object::getReferenceFor();
            return null !== $this->findBoundedContext(
                $apieSerializerContext,
                $resourceClass
            );
        }

        return false;
    }
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): ?ItemHashmap
    {
        assert($object instanceof IdentifierInterface);
        $contextId = $this->findBoundedContext($apieSerializerContext, $object::getReferenceFor());
        $dataLayer = $apieSerializerContext->getContext()->getContext(ApieDatalayer::class);
        assert($dataLayer instanceof ApieDatalayer);
        try {
            $relation = $dataLayer->find($object, $contextId);
        } catch (EntityNotFoundException) {
            return null;
        }
        return $apieSerializerContext->normalizeAgain($relation, true);
    }
}
<?php
namespace Apie\Serializer\Normalizers;

use Apie\Common\ContextConstants;
use Apie\Core\BoundedContext\BoundedContext;
use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\ApieDatalayerWithSupport;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use ReflectionClass;

class IdentifierNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        // implementation is still buggy so it's deliberately not working right now
        $apieContext = $apieSerializerContext->getContext();
        if (class_exists($desiredType) && $apieContext->hasContext('TODO') && $apieContext->hasContext(ApieDatalayer::class) && $apieContext->hasContext(ContextConstants::BOUNDED_CONTEXT_ID)) {
            $refl = new ReflectionClass($desiredType);
            $datalayer = $apieContext->getContext(ApieDatalayer::class);
            $boundedContextId = BoundedContextId::fromNative($apieContext->getContext(ContextConstants::BOUNDED_CONTEXT_ID));
            return $refl->implementsInterface(IdentifierInterface::class)
                && (!$datalayer instanceof ApieDatalayerWithSupport || $datalayer->isSupported($refl, $boundedContextId));
        }
        return false;
    }

    /**
     * @template T of IdentifierInterface<EntityInterface>
     * @param class-string<T> $desiredType
     * @return T
     */
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): IdentifierInterface
    {
        $identifier = $desiredType::fromNative($object);
        $datalayer = $apieSerializerContext->getContext()->getContext(ApieDatalayer::class);
        $boundedContext = $apieSerializerContext->getContext()->getContext(BoundedContext::class);
        $datalayer->find($identifier, $boundedContext);
        return $identifier;
    }
}

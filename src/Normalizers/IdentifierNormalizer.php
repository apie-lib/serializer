<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\BoundedContext\BoundedContextId;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\ApieDatalayer;
use Apie\Core\Datalayers\ApieDatalayerWithSupport;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Identifiers\IdentifierInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

class IdentifierNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        $apieContext = $apieSerializerContext->getContext();
        $hierarchy = $apieContext->getContext('hierarchy', false) ?? [];
        $fieldName = array_pop($hierarchy);
        if (class_exists($desiredType) && $fieldName !== 'id' && $apieContext->hasContext(ApieDatalayer::class) && $apieContext->hasContext(ContextConstants::BOUNDED_CONTEXT_ID)) {
            $refl = new ReflectionClass($desiredType);
            $datalayer = $apieContext->getContext(ApieDatalayer::class);
            $boundedContextId = new BoundedContextId($apieContext->getContext(ContextConstants::BOUNDED_CONTEXT_ID));
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
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): IdentifierInterface
    {
        $identifier = $desiredType::fromNative($object);
        $datalayer = $apieSerializerContext->getContext()->getContext(ApieDatalayer::class);
        $boundedContextId = $apieSerializerContext->getContext()->getContext(ContextConstants::BOUNDED_CONTEXT_ID);
        $datalayer->find($identifier, BoundedContextId::fromNative($boundedContextId));
        return $identifier;
    }
}

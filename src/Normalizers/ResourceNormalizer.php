<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Actions\ActionResponse;
use Apie\Core\ContextConstants;
use Apie\Core\Datalayers\Lists\PaginatedResult;
use Apie\Core\Entities\EntityInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\PropertyAccess;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnitEnum;

class ResourceNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return is_resource($object) || $object instanceof UploadedFile || $object instanceof UploadedFileInterface;
    }

    /**
     * @param resource|UploadedFile|UploadedFileInterface $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): ?string
    {
        $apieContext = $apieSerializerContext->getContext();
        $actionResponse = $apieContext->getContext(ActionResponse::class);
        assert($actionResponse instanceof ActionResponse);
        assert(isset($actionResponse->result));
        $hierarchy = $apieContext->getContext('hierarchy', false) ?? [];
        $result = $actionResponse->result;
    
        if ($result instanceof PaginatedResult) {
            $index = array_shift($hierarchy);
            $result = PropertyAccess::getPropertyValue($result->list, [$index], $apieContext, false);
        }
        if ($result instanceof EntityInterface) {
            $boundedContextId = $apieContext->getContext(ContextConstants::BOUNDED_CONTEXT_ID);
            $resourceClass = new ReflectionClass($apieContext->getContext(ContextConstants::RESOURCE_NAME));
            return '/' . $boundedContextId . '/' . $resourceClass->getShortName() . '/' . $result->getId()->toNative() . '/' . implode('/', $hierarchy);
        }
        
        return null;
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType ===  'resource';
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): UnitEnum
    {
        throw new \LogicException('not implemented yet');
    }
}

<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Actions\ActionResponse;
use Apie\Core\ContextConstants;
use Apie\Core\Enums\DoNotChangeUploadedFile;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Special normalizer related to not changing files.
 */
class DoNotChangeFileNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        if ($apieSerializerContext->getContext()->hasContext(ActionResponse::class)) {
            return false;
        }
        if (!$apieSerializerContext->getContext()->hasContext(ContextConstants::EDIT_OBJECT)
            && !$apieSerializerContext->getContext()->hasContext(ContextConstants::REPLACE_OBJECT)) {
            return false;
        }
        if (!$apieSerializerContext->getContext()->hasContext(ContextConstants::DISPLAY_FORM)) {
            return false;
        }
        return is_resource($object)
            || $object instanceof UploadedFileInterface
            || get_debug_type($object) === 'resource (closed)';
    }

    /**
     * @param resource|UploadedFileInterface $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string
    {
        return DoNotChangeUploadedFile::DoNotChange->value;
    }
}

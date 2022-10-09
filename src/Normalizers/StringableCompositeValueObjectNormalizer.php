<?php
namespace Apie\Serializer\Normalizers;

use Apie\Common\ContextConstants;
use Apie\CompositeValueObjects\CompositeValueObject;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;
use ReflectionClass;
use Stringable;

class StringableCompositeValueObjectNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        $context = $apieSerializerContext->getContext();
        if ($context->hasContext(ContextConstants::GET_ALL_OBJECTS) && $context->hasContext(ContextConstants::CMS)
            && $object instanceof ValueObjectInterface
            && $object instanceof Stringable) {
            $refl = new ReflectionClass($object);
            return in_array(CompositeValueObject::class, $refl->getTraitNames());
        }
        return false;
    }

    /**
     * @param Stringable&ValueObjectInterface $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string
    {
        return (string) $object;
    }
}

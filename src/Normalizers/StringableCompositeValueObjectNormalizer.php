<?php
namespace Apie\Serializer\Normalizers;

use Apie\Common\ContextConstants;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\CompositeValueObject;
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
        $displayAsString = ($context->hasContext(ContextConstants::GET_ALL_OBJECTS) && $context->hasContext(ContextConstants::CMS))
            || $context->hasContext(ContextConstants::SHOW_PROFILE);
        if ($displayAsString && $object instanceof Stringable) {
            if ($object instanceof ValueObjectInterface) {
                $refl = new ReflectionClass($object);
                return in_array(CompositeValueObject::class, $refl->getTraitNames());
            }
            return $object instanceof ItemList || $object instanceof ItemHashmap;
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

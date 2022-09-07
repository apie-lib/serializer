<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Entities\PolymorphicEntityInterface;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Other\DiscriminatorMapping;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\NormalizerInterface;
use ReflectionClass;

class PolymorphicEntityNormalizer implements NormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof PolymorphicEntityInterface;
    }

    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): ItemHashmap
    {
        $result = $apieSerializerContext->normalizeAgain($object, true);
        $refl = new ReflectionClass($object);
        while ($refl) {
            $method = $refl->getMethod('getDiscriminatorMapping');
            if ($method->getDeclaringClass()->name === $refl->name && !$method->isAbstract()) {
                /** @var DiscriminatorMapping $mapping */
                $mapping = $method->invoke(null);
                $propertyName = $mapping->getPropertyName();
                $value = $mapping->getDiscriminatorForObject($object);
                $result[$propertyName] = $value;
            }
            $refl = $refl->getParentClass();
        }
        return $result;
    }
}

<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Exceptions\ItemCanNotBeNormalizedInCurrentContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use ReflectionEnum;
use UnitEnum;

class EnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof UnitEnum;
    }

    /**
     * @var UnitEnum $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int
    {
        return $object->value ?? $object->key;
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return is_a($desiredType, UnitEnum::class, true) && in_array(get_debug_type($object), ['int', 'string']);
    }

    /**
     * @var string|int $object
     * @var class-string<UnitEnum> $desiredType
     */
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap $object, string $desiredType, ApieSerializerContext $apieSerializerContext): UnitEnum
    {
        $refl = new ReflectionEnum($desiredType);
        if ($refl->isBacked()) {
            $enum = $desiredType::from($object);
        } else {
            $enum = $refl->getCase($object)->getValue();
        }
        $refl = $refl->getCase($enum->name);
        if (!$apieSerializerContext->getContext()->isFiltered($refl)) {
            throw new ItemCanNotBeNormalizedInCurrentContext($enum);
        }
        return $enum;
    }
}

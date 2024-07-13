<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Exceptions\InvalidStringForValueObjectException;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Exceptions\ItemCanNotBeNormalizedInCurrentContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;
use ReflectionEnum;
use UnitEnum;

class EnumNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof UnitEnum;
    }

    /**
     * @param UnitEnum $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int
    {
        return $object->value ?? $object->name;
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return is_a($desiredType, UnitEnum::class, true) && in_array(get_debug_type($object), ['int', 'string']);
    }

    /**
     * @param string|int $object
     * @param class-string<UnitEnum> $desiredType
     */
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): UnitEnum
    {
        $object = Utils::toString($object);
        $refl = new ReflectionEnum($desiredType);
        $enum = null;
        foreach ($refl->getCases() as $case) {
            if ($refl->isBacked()) {
                if (((string) $case->getBackingValue()) === $object) {
                    $enum = $case->getValue();
                }
            }
            if ($case->name === $object) {
                $enum = $case->getValue();
                break;
            }
        }
        if (!$enum) {
            throw new InvalidStringForValueObjectException($object, new ReflectionClass($desiredType));
        }
        $refl = $refl->getCase($enum->name);
        if (!$apieSerializerContext->getContext()->appliesToContext($refl)) {
            throw new ItemCanNotBeNormalizedInCurrentContext($enum);
        }
        return $enum;
    }
}

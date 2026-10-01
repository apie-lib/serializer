<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

class ReflectionClassNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function supportsDenormalization(
        string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return in_array(
            $desiredType,
            [
                ReflectionClass::class
            ]
        );
    }

    /**
     * @return ReflectionClass<covariant object>
     */
    public function denormalize(
        string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): ReflectionClass {
        return new ReflectionClass(Utils::toString($object));
    }
    public function supportsNormalization(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return $object instanceof ReflectionClass;
    }
    public function normalize(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): string|int|float|bool|null|ItemList|ItemHashmap {
        assert($object instanceof ReflectionClass);
        return $object->getName();
    }
}

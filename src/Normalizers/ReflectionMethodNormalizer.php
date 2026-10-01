<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionMethod;

class ReflectionMethodNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function supportsDenormalization(
        string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return in_array(
            $desiredType,
            [
                ReflectionMethod::class
            ]
        );
    }
    public function denormalize(
        string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object,
        string $desiredType,
        ApieSerializerContext $apieSerializerContext
    ): ReflectionMethod {
        return ReflectionMethod::createFromMethodName(Utils::toString($object));
    }
    public function supportsNormalization(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): bool {
        return $object instanceof ReflectionMethod;
    }
    public function normalize(
        mixed $object,
        ApieSerializerContext $apieSerializerContext
    ): string|int|float|bool|null|ItemList|ItemHashmap {
        assert($object instanceof ReflectionMethod);
        return $object->getDeclaringClass()->name . '::' . $object->getName();
    }
}

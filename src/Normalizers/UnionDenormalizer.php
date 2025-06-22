<?php

namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionIntersectionType;
use ReflectionUnionType;

class UnionDenormalizer implements DenormalizerInterface
{

    public function supportsDenormalization(float|bool|int|string|UploadedFileInterface|ItemHashmap|ItemList|null $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        if (strpos($desiredType, '&') !== false || strpos($desiredType, '|') !== false) {
            try {
                $type = ReflectionTypeFactory::createReflectionType($desiredType);
                return $type instanceof ReflectionUnionType || $type instanceof ReflectionIntersectionType;
            } catch (\Throwable) {
                return false;
            }
        }
        return false;
    }

    public function denormalize(float|bool|int|string|UploadedFileInterface|ItemHashmap|ItemList|null $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        $type = ReflectionTypeFactory::createReflectionType($desiredType);
        return $apieSerializerContext->denormalizeFromTypehint(
            $object,
            $type
        );
    }
}

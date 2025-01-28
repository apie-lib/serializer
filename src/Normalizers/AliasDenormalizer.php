<?php

namespace Apie\Serializer\Normalizers;

use Apie\Core\ApieLib;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\UploadedFileInterface;

class AliasDenormalizer implements DenormalizerInterface
{

    public function supportsDenormalization(float|bool|int|string|UploadedFileInterface|ItemHashmap|ItemList|null $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return ApieLib::hasAlias($desiredType);
    }

    public function denormalize(float|bool|int|string|UploadedFileInterface|ItemHashmap|ItemList|null $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        $newDesiredType = ApieLib::getAlias($desiredType);
        return $apieSerializerContext->denormalizeFromTypehint(
            $object,
            ReflectionTypeFactory::createReflectionType($newDesiredType)
        );
    }
}
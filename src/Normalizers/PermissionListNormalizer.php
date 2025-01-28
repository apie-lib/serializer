<?php

namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Lists\PermissionList;
use Apie\Core\Permissions\PermissionInterface;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Apie\Serializer\Lists\SerializedList;
use Psr\Http\Message\UploadedFileInterface;

class PermissionListNormalizer implements NormalizerInterface, DenormalizerInterface
{

    public function supportsDenormalization(float|bool|int|string|UploadedFileInterface|ItemHashmap|ItemList|null $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === PermissionList::class;
    }

    public function denormalize(float|bool|int|string|UploadedFileInterface|ItemHashmap|ItemList|null $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        $list = Utils::toArray($object);
        return new PermissionList($list);
    }

    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof PermissionList;
    }

    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap
    {
        assert($object instanceof PermissionList);
        return new SerializedList($object->toStringList()->toArray());
    }
}
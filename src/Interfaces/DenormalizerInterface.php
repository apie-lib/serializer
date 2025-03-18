<?php
namespace Apie\Serializer\Interfaces;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Serializer;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(Serializer::class)]
interface DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool;
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed;
}

<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

class UploadedFileNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        if (in_array($desiredType, [UploadedFileInterface::class, StoredFile::class])) {
            return true;
        }
        if (!class_exists($desiredType)) {
            return false;
        }
        $class = new ReflectionClass($desiredType);
        return in_array(UploadedFileInterface::class, $class->getInterfaceNames());
    }
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): UploadedFileInterface
    {
        if ($object instanceof UploadedFileInterface) {
            return $object;
        }
        $array = Utils::toArray($object);
        $className = $desiredType === UploadedFileInterface::class ? StoredFile::class : $desiredType;
        return $className::createFromString(
            $array['contents'],
            $array['mime'] ?? 'application/octet-stream',
            $array['originalFilename'],
        );
    }
}

<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\FileStorage\StoredFile;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\JsonFileUpload;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Exceptions\FileUploadException;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
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
            if ($object->getError() !== UPLOAD_ERR_OK) {
                throw new FileUploadException($object);
            }
            return $object;
        }
        $array = Utils::toArray($object);
        /** @var JsonFileUpload $object */
        $object = $apieSerializerContext->denormalizeFromTypehint(
            $array,
            ReflectionTypeFactory::createReflectionType(JsonFileUpload::class)
        );
        /** @var class-string<StoredFile> $className */
        $className = $desiredType === UploadedFileInterface::class ? StoredFile::class : $desiredType;
        return $object->toUploadedFile($className);
    }
}

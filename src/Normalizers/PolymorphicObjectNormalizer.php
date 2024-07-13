<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Exceptions\IndexNotFoundException;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\Other\DiscriminatorMapping;
use Apie\Core\Utils\EntityUtils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Exceptions\ValidationException;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\TypeConverter\ReflectionTypeFactory;
use Psr\Http\Message\UploadedFileInterface;
use ReflectionClass;

class PolymorphicObjectNormalizer implements DenormalizerInterface
{
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        if ($object instanceof ItemHashmap && EntityUtils::isPolymorphicEntity($desiredType)) {
            $refl = new ReflectionClass($desiredType);
            return ($refl->getMethod('getDiscriminatorMapping')->getDeclaringClass()->name === $desiredType);
        }

        return false;
    }

    /**
     * @param ItemHashMap $object
     */
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): mixed
    {
        $refl = new ReflectionClass($desiredType);
        /** @var DiscriminatorMapping $mapping */
        $mapping = $refl->getMethod('getDiscriminatorMapping')->invoke(null);
        $propertyName = $mapping->getPropertyName();
        if (!isset($object[$propertyName])) {
            throw ValidationException::createFromArray(['id' => new IndexNotFoundException($propertyName)]);
        }
        $className = $mapping->getClassNameFromDiscriminator($object[$propertyName]);
        return $apieSerializerContext->denormalizeFromTypehint($object, ReflectionTypeFactory::createReflectionType($className));
    }
}

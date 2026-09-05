<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use FFI;
use FFI\CData;
use Psr\Http\Message\UploadedFileInterface;

class FfiCdataNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof CData;
    }

    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap
    {
        $typeName = FFI::typeof($object)->getName();
        if (preg_match('/^(.+)\[(\d+)\]$/', $typeName, $matches)) {
            $values = [];
            for ($index = 0; $index < (int) $matches[2]; $index++) {
                $values[] = $object[$index];
            }
            return new ItemList($values);
        }

        return $object->cdata;
    }

    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === CData::class;
    }

    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): CData
    {
        if ($object instanceof ItemList) {
            $values = $object->toArray();
            $type = self::getType($values[0] ?? null);
            $result = FFI::cdef()->new($type . '[' . count($values) . ']');
            foreach ($values as $index => $value) {
                $result[$index] = $value;
            }
            return $result;
        }

        $result = FFI::cdef()->new(self::getType($object));
        // @phpstan-ignore-next-line property.notFound
        $result->cdata = $object;
        return $result;
    }

    private static function getType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'bool',
            is_int($value) => 'int',
            is_float($value) => 'double',
            default => 'char',
        };
    }
}

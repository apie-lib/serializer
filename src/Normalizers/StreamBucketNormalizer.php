<?php
namespace Apie\Serializer\Normalizers;

use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Core\ValueObjects\Utils;
use Apie\Serializer\Context\ApieSerializerContext;
use Apie\Serializer\Interfaces\DenormalizerInterface;
use Apie\Serializer\Interfaces\NormalizerInterface;
use Psr\Http\Message\UploadedFileInterface;
use StreamBucket;

class StreamBucketNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function supportsNormalization(mixed $object, ApieSerializerContext $apieSerializerContext): bool
    {
        return $object instanceof StreamBucket;
    }

    /**
     * @param StreamBucket $object
     */
    public function normalize(mixed $object, ApieSerializerContext $apieSerializerContext): string|int|float|bool|null|ItemList|ItemHashmap
    {
        return new ItemHashmap([
            'data' => $object->data,
            'dataLength' => $object->dataLength,
        ]);
    }
    public function supportsDenormalization(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): bool
    {
        return $desiredType === StreamBucket::class;
    }
    public function denormalize(string|int|float|bool|null|ItemList|ItemHashmap|UploadedFileInterface $object, string $desiredType, ApieSerializerContext $apieSerializerContext): StreamBucket
    {
        if ($object instanceof ItemHashmap) {
            $len = $object['dataLength'] ?? null;
            $object = Utils::toString($object['data']);
            if ($len !== null) {
                $object = substr($object, 0, $len);
            }
            $stream = fopen('php://memory', 'r+');
            return stream_bucket_new($stream, $object);
        }
        $object = Utils::toString($object);
        $stream = fopen('php://memory', 'r+');
        return stream_bucket_new($stream, $object);
    }

}

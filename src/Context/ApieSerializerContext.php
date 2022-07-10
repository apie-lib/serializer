<?php
namespace Apie\Serializer\Context;

use Apie\Core\Context\ApieContext;
use Apie\Core\Lists\ItemHashmap;
use Apie\Core\Lists\ItemList;
use Apie\Serializer\Serializer;

final class ApieSerializerContext
{
    public function __construct(private Serializer $serializer, private ApieContext $apieContext)
    {
    }

    public function normalizeAgain(mixed $object): string|int|float|bool|ItemList|ItemHashmap|null
    {
        return $this->serializer->normalize($object, $this->apieContext);
    }

    public function normalizeChildElement(string $key, mixed $object): string|int|float|bool|ItemList|ItemHashmap|null
    {
        $hierarchy = [];
        if ($this->apieContext->hasContext('hierarchy')) {
            $hierarchy = $this->apieContext->getContext('hierarchy');
        }
        $hierarchy[] = $key;
        $newContext = $this->apieContext->withContext('hierarchy', $hierarchy);
        return $this->serializer->normalize($object, $newContext);
    }

    public function getContext(): ApieContext
    {
        return $this->apieContext;
    }
}

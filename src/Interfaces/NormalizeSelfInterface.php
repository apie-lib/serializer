<?php
namespace Apie\Serializer\Interfaces;

use Apie\Serializer\Context\ApieSerializerContext;

interface NormalizeSelfInterface
{
    public function normalize(ApieSerializerContext $apieSerializerContext): mixed;
}

<?php
namespace Apie\Serializer\Context;

use Apie\Core\Context\ApieContext;

final class ApieSerializerContext
{
    public function __construct(private ApieContext $apieContext)
    {
    }
}
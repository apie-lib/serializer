<?php
namespace Apie\Serializer\Context;

use Apie\Core\Context\ApieContext;
use Apie\Core\ContextBuilders\ContextBuilderInterface;
use Psr\Http\Message\ServerRequestInterface;

class AddFilterContext implements ContextBuilderInterface
{
    public function process(ApieContext $context): ApieContext
    {
        $request = $context->getContext(ServerRequestInterface::class, false);
        if ($request instanceof ServerRequestInterface) {
            //
        }

        return $context;
    }
}

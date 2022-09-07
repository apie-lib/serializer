<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Exceptions\ApieException;
use Apie\Core\ValueObjects\Utils;

class ItemCanNotBeNormalizedInCurrentContext extends ApieException
{
    public function __construct(mixed $input)
    {
        parent::__construct(sprintf(
            'Item can not be normalized in current context "%s"',
            Utils::displayMixedAsString($input)
        ));
    }
}

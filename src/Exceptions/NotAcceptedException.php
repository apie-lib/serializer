<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Exceptions\ApieException;

class NotAcceptedException extends ApieException
{
    public function __construct(string $acceptHeader)
    {
        parent::__construct(sprintf('Accept header: "%s" not accepted!', $acceptHeader));
    }
}

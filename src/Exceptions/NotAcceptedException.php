<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;

class NotAcceptedException extends ApieException implements HttpStatusCodeException
{
    public function __construct(string $acceptHeader)
    {
        parent::__construct(sprintf('Accept header: "%s" not accepted!', $acceptHeader));
    }

    public function getStatusCode(): int
    {
        return 406;
    }
}

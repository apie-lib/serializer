<?php

use Apie\Core\Exceptions\ApieException;
use Apie\Core\Exceptions\HttpStatusCodeException;

class ValidationException extends ApieException implements HttpStatusCodeException
{
    public function getStatusCode(): int
    {
        return 422;
    }
}

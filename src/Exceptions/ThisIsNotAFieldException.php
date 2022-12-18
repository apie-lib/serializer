<?php
namespace Apie\Serializer\Exceptions;

use Apie\Core\Exceptions\ApieException;

class ThisIsNotAFieldException extends ApieException
{
    public function __construct(string $fieldName) {
        parent::__construct('"' . $fieldName . '" is not a field you can set');
    }
}
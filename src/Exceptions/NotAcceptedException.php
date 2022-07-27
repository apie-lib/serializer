<?php

use Apie\Core\Exceptions\ApieException;

class NotAcceptedException extends ApieException
{
    public function __construct()
    {
        parent::__construct('Not accepted');
    }
}

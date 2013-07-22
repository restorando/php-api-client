<?php

namespace Restorando\Exception;

/**
 * ValidationFailedException
 *
 */
class ValidationFailedException extends ErrorException
{

    public function __construct($message, $code = 0, $errors = array())
    {
        parent::__construct($message, $code);
        $this->errors = $errors;
    }
}

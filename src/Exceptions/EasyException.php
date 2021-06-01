<?php

namespace Easy\Exceptions;

use Exception;

class EasyException extends Exception
{
    /**
     * @param string $message
     * @throws EasyException
     */
    static function throwException(string $message)
    {
        throw new EasyException($message);
    }
}

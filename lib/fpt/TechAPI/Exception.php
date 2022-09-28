<?php

namespace DiagVN\Fpt\TechAPI;

class Exception extends \Exception
{
    public function __construct($message, $code = null, $previous = null)
    {
        if (Constant::isWriteLog()) {
            LogWriter::getInstance()->log($message, $code);
        }

        parent::__construct($message, $code, $previous);
    }
}

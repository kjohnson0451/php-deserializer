<?php
/********************************************************************************************************************************
 * Author: Keith Johnson
 ********************************************************************************************************************************/

namespace KeithParser;

class KeithParsingError extends \Exception
{
    /**
     * @param int $char
     * @param string $message
     */
    public function __construct($char, $message)
    {
        parent::__construct("Parsing error at $char. " . $message . ".");
    }
}

<?php
/********************************************************************************************************************************
 * Author: Keith Johnson
 ********************************************************************************************************************************/

namespace KeithParser;

require_once('KeithParsingError.php');

class Parser
{
    const STATE_START      = 0;
    const STATE_END        = 1;
    const STATE_IN_ARRAY   = 2;
    const STATE_IN_STRING  = 3;
    const STATE_IN_ESCAPED = 4;

    const BOOL_TRUE        = "true";
    const BOOL_FALSE       = "false";
    const BOOL_TRUE_LEN    = 4;
    const BOOL_FALSE_LEN   = 5;

    /**
     * @var string
     */
    private $input;

    /**
     * @var int
     */
    private $inputLength;

    private $result;

    /**
     * @var int
     */
    private $state;

    private $arrayStack;
    
    /**
     * @var string
     */
    private $buffer;

    /**
     * @var int
     */
    private $charNumber;

    /**
     * @param string $input
     */
    public function __construct($input) {
        $this->input = $input;
        $this->inputLength = strlen($input);
        $this->result = NULL;
        $this->state = self::STATE_START;
        $this->arrayStack = new \SplStack();
        $this->buffer = NULL;
        $this->charNumber = 1;
    }

    /**
     * @throws ParsingError
     */
    public function parse() {

        for( $i = 0; $i < $this->inputLength; $i++ ) {
            $c = $this->input[$i];

            if( $this->state === self::STATE_START ) {
                if( $c === " " || $c === "\n") {
                } elseif( $c === '[' ) {
                    $this->startArray();
                } elseif( $c === "'") {
                    $this->startString();
                } elseif( substr($this->input, $i, self::BOOL_TRUE_LEN) === self::BOOL_TRUE ) {
                    $this->addTrue();
                    $i += (self::BOOL_TRUE_LEN - 1);
                    $this->charNumber = $this->charNumber + self::BOOL_TRUE_LEN - 1;                    
                } elseif( substr($this->input, $i, self::BOOL_FALSE_LEN) === self::BOOL_FALSE ) {
                    $this->addFalse();
                    $i += (self::BOOL_FALSE_LEN - 1);
                    $this->charNumber = $this->charNumber + self::BOOL_FALSE_LEN - 1;
                } else {
                    $this->throwParseError("Unrecognized character '" . $c . "'");
                }
            } elseif( $this->state === self::STATE_IN_ARRAY ) {
                if( $c === " " || $c === "\n" ) {
                } elseif ( $c === "'") {
                    $this->startString();
                } elseif( substr($this->input, $i, self::BOOL_TRUE_LEN) === self::BOOL_TRUE ) {
                    $this->addTrue();
                    $i += (self::BOOL_TRUE_LEN - 1);
                    $this->charNumber = $this->charNumber + self::BOOL_TRUE_LEN - 1;
                } elseif( substr($this->input, $i, self::BOOL_FALSE_LEN) === self::BOOL_FALSE ) {
                    $this->addFalse();
                    $i += (self::BOOL_FALSE_LEN - 1);
                    $this->charNumber = $this->charNumber + self::BOOL_FALSE_LEN - 1;
                } elseif( $c === '[' ) {
                    $this->startArray();
                } elseif( $c === ']' ) {
                    $this->endArray();
                } elseif( $c === ',' ) {
                    $this->addToArray();
                } else {
                    $this->throwParseError("Unrecognized character '" . $c . "'");
                }
            } elseif( $this->state === self::STATE_IN_STRING ) {
                if ( ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z')
                     || $c === '[' || $c === ']' || $c === ' ' || $c === ',' ) {
                    $this->addToString($c);
                } elseif( $c === "\\" ) {
                    $this->state = self::STATE_IN_ESCAPED;
                } elseif( $c === "'" ) {
                    $this->endString();
                } else {
                    $this->throwParseError("Unrecognized character '" . $c . "'");
                }
            } elseif( $this->state === self::STATE_IN_ESCAPED ) {
                if ( ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z')
                     || $c === '[' || $c === ']' || $c === ' ' || $c === ',' || $c === "'" || $c === "\\") {
                    $this->addToString($c);
                    $this->state = self::STATE_IN_STRING;
                } else {
                    $this->throwParseError("Unrecognized character '" . $c . "'");
                }
            } elseif( $this->state === self::STATE_END ) {
                break;
            }

            if( ($i === $this->inputLength - 1) && $this->state !== self::STATE_END) {
                if( $this->state === self::STATE_IN_ARRAY ) {
                    $this->throwParseError("Failed to close off array with a ]");
                } elseif( $this->state === self::STATE_IN_STRING ) {
                    $this->throwParseError("Failed to close off string with a '");
                }
            }

            $this->charNumber = $this->charNumber + 1;
        }

        return $this->result;
    }

    private function startArray() {
        $this->state = self::STATE_IN_ARRAY;

        $newArray = array();        
        $this->arrayStack->push($newArray);
    }

    private function endArray() {

        $this->addToArray();
        
        $currentArray = $this->arrayStack->pop();

        if( $this->arrayStack->isEmpty() ) {
            $this->state = self::STATE_END;
            $this->result = $currentArray;
        } else {
            $parentArray = $this->arrayStack->pop();
            array_push($parentArray, $currentArray);
            $this->arrayStack->push($parentArray);
        }
    }

    private function addToArray() {
        if( $this->buffer != NULL ) {
            $currentArray = $this->arrayStack->pop();
            array_push($currentArray, $this->buffer);
            $this->arrayStack->push($currentArray);
        }
        $this->buffer = NULL;
    }

    private function startString() {
        $this->state = self::STATE_IN_STRING;
        $this->buffer = "";
    }

    private function endString() {
        if( $this->arrayStack->isEmpty() ) {
            $this->state = self::STATE_END;
            $this->result = $this->buffer;            
        } else {
            $this->state = self::STATE_IN_ARRAY;
        }
    }

    /**
     * @param string $c
     */
    private function addToString( $c ) {
        $this->buffer .= $c;
    }
    
    private function addTrue() {
        if( $this->arrayStack->isEmpty() ) {
            $this->state = self::STATE_END;
            $this->result = true;
        } else {
            $currentArray = $this->arrayStack->pop();
            array_push($currentArray, true);
            $this->arrayStack->push($currentArray);
        }
    }

    private function addFalse() {
        if( $this->arrayStack->isEmpty() ) {
            $this->state = self::STATE_END;
            $this->result = false;
        } else {
            $currentArray = $this->arrayStack->pop();
            array_push($currentArray, false);
            $this->arrayStack->push($currentArray);
        }
    }

    /**
     * @param string $message
     */
    private function throwParseError($message)
    {
        throw new KeithParsingError(
            $this->charNumber,
            $message
        );
    }
}
?>
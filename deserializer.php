<?php

/********************************************************************************************************************************
 * Author: Keith Johnson
 ********************************************************************************************************************************/

require_once('src/KeithParser.php');

try {
    if( $argc < 2 ) {
        throw new Exception(__FILE__ . " requires 1 or more arguments.");
    }
} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    exit(1);
}

$input = $argv[1];

try {
    $parser = new \KeithParser\Parser($input);
    $output = $parser->parse();    
    var_dump($output);    
} catch (Exception $e) {
    error_log("ERROR: " . $e->getMessage());
    exit(2);
}

?>

<?php
namespace KeithParser;

use PHPUnit\Framework\TestCase;

require_once('src/KeithParser.php');

class KeithParserTest extends TestCase
{
    public function testBool()
    {
        $input = "true";
        $parser = new \KeithParser\Parser($input);
        $this->assertEquals(true, $parser->parse());
    }

    public function testString()
    {
        $input = "'true'";
        $parser = new \KeithParser\Parser($input);
        $this->assertEquals("true", $parser->parse());
    }

    public function testArray()
    {
        $input = "['something', true]";
        $parser = new \KeithParser\Parser($input);
        $this->assertEquals(array("something", true), $parser->parse());
    }

    public function testNestedArray()
    {
        $input = "['something', true, ['blah', 'blah blah'] ]";
        $parser = new \KeithParser\Parser($input);
        $this->assertEquals(array("something", true, array("blah", "blah blah")), $parser->parse());
    }
}
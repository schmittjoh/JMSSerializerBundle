<?php

namespace JMS\SerializerBundle\Tests\Serializer;

use JMS\SerializerBundle\Serializer\TypeParser;

class TypeParserTest extends \PHPUnit_Framework_TestCase
{
    private $parser;

    /**
     * @dataProvider getTypes
     */
    public function testParse($type, $name, array $params = array())
    {
        $this->assertEquals(array('name' => $name, 'params' => $params), $this->parser->parse($type));
    }

    public function getTypes()
    {
        $types = array();
        $types[] = array('string', 'string');
        $types[] = array('array<Foo>', 'array', array(array('name' => 'Foo', 'params' => array())));
        $types[] = array('array<Foo,Bar>', 'array', array(array('name' => 'Foo', 'params' => array()), array('name' => 'Bar', 'params' => array())));
        $types[] = array('array<Foo\Bar, Baz\Boo>', 'array', array(array('name' => 'Foo\Bar', 'params' => array()), array('name' => 'Baz\Boo', 'params' => array())));
        $types[] = array('a<b<c,d>,e>', 'a', array(array('name' => 'b', 'params' => array(array('name' => 'c', 'params' => array()), array('name' => 'd', 'params' => array()))), array('name' => 'e', 'params' => array())));
        $types[] = array('Foo', 'Foo');
        $types[] = array('Foo\Bar', 'Foo\Bar');
        $types[] = array('Foo<"asdf asdf">', 'Foo', array('asdf asdf'));

        return $types;
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected token T_CLOSE_BRACKET, but reached end of type.
     */
    public function testParamTypeMustEndWithBracket()
    {
        $this->parser->parse('Foo<bar');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected token T_NAME, but got T_COMMA at position 0.
     */
    public function testMustStartWithName()
    {
        $this->parser->parse(',');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected any of T_NAME or T_STRING, but got T_CLOSE_BRACKET at position 4.
     */
    public function testEmptyParams()
    {
        $this->parser->parse('Foo<>');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected any of T_NAME or T_STRING, but got T_CLOSE_BRACKET at position 7.
     */
    public function testNoTrailingComma()
    {
        $this->parser->parse('Foo<aa,>');
    }

    protected function setUp()
    {
        $this->parser = new TypeParser();
    }
}
<?php

namespace JMS\SerializerBundle\Tests\Serializer\Naming;

use JMS\SerializerBundle\Metadata\PropertyMetadata;
use JMS\SerializerBundle\Metadata\ClassMetadata;
use JMS\SerializerBundle\Serializer\Naming\CamelCaseNamingStrategy;

class CamelCaseNamingStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultCamelCaseNamingStrategy()
    {
        $camelCaseNamingStrategy = new CamelCaseNamingStrategy();

        $metadata = new ClassMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata');
        $metadata->addPropertyMetadata(new PropertyMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata', 'camelCaseProperty'));

        $translatedPropertyName = $camelCaseNamingStrategy->translateName($metadata->propertyMetadata['camelCaseProperty']);

        $this->assertEquals($translatedPropertyName, 'camel_case_property');
    }

    public function testLowerCaseNamingStrategy()
    {
        $separator = '';
        $lowerCase = true;
        $lowerCamelCase = true;

        $camelCaseNamingStrategy = new CamelCaseNamingStrategy($separator, $lowerCase, $lowerCamelCase);

        $metadata = new ClassMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata');
        $metadata->addPropertyMetadata(new PropertyMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata', 'camelCaseProperty'));

        $translatedPropertyName = $camelCaseNamingStrategy->translateName($metadata->propertyMetadata['camelCaseProperty']);

        $this->assertEquals($translatedPropertyName, 'camelcaseproperty');
    }

    public function testLowerCamelCaseNamingStrategy()
    {
        $separator = '';
        $lowerCase = false;
        $lowerCamelCase = true;

        $camelCaseNamingStrategy = new CamelCaseNamingStrategy($separator, $lowerCase, $lowerCamelCase);

        $metadata = new ClassMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata');
        $metadata->addPropertyMetadata(new PropertyMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata', 'camelCaseProperty'));

        $translatedPropertyName = $camelCaseNamingStrategy->translateName($metadata->propertyMetadata['camelCaseProperty']);

        $this->assertEquals($translatedPropertyName, 'camelCaseProperty');
    }

    public function testUpperCamelCaseNamingStrategy()
    {
        $separator = '';
        $lowerCase = false;
        $lowerCamelCase = false;

        $camelCaseNamingStrategy = new CamelCaseNamingStrategy($separator, $lowerCase, $lowerCamelCase);

        $metadata = new ClassMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata');
        $metadata->addPropertyMetadata(new PropertyMetadata('JMS\SerializerBundle\Tests\Serializer\Naming\camelCasePropertyMetadata', 'camelCaseProperty'));

        $translatedPropertyName = $camelCaseNamingStrategy->translateName($metadata->propertyMetadata['camelCaseProperty']);

        $this->assertEquals($translatedPropertyName, 'CamelCaseProperty');
    }
}

class CamelCasePropertyMetadata
{
    private $camelCaseProperty;
}
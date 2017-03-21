<?php

namespace JMS\SerializerBundle\Tests\ContextFactory;

use JMS\SerializerBundle\ContextFactory\ConfiguredContextFactory;

/**
 * Class ConfiguredContextFactoryTest
 */
class ConfiguredContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateSerializationContext()
    {
        $config = $this->getContextConfig();
        $object = new ConfiguredContextFactory($config);

        $this->assertInstanceOf('JMS\Serializer\ContextFactory\SerializationContextFactoryInterface', $object);

        $context = $object->createSerializationContext();
        $this->assertInstanceOf('JMS\Serializer\SerializationContext', $context);
        $this->assertSame($config['serialize_null'], $context->shouldSerializeNull());

        $this->assertSame($config['version'], $context->attributes->get('version')->get());
        $this->assertSame($config['groups'], $context->attributes->get('groups')->get());
    }

    public function testCreateDeserializationContext()
    {
        $config = $this->getContextConfig();
        $object = new ConfiguredContextFactory($config);

        $this->assertInstanceOf('JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface', $object);

        $context = $object->createDeserializationContext();
        $this->assertInstanceOf('JMS\Serializer\DeserializationContext', $context);
        $this->assertSame($config['serialize_null'], $context->shouldSerializeNull());

        $this->assertSame($config['version'], $context->attributes->get('version')->get());
        $this->assertSame($config['groups'], $context->attributes->get('groups')->get());
    }

    private function getContextConfig()
    {
        return [
            'attributes'     => [
                'x' => mt_rand(0, PHP_INT_MAX),
            ],
            'groups'         => [ 'Default', 'Registration' ],
            'serialize_null' => true,
            'version'        => sprintf('%d.%d.%d', mt_rand(1, 10), mt_rand(1, 10), mt_rand(1, 10)),
        ];
    }
}

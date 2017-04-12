<?php

namespace JMS\SerializerBundle\Tests\ContextFactory;

use JMS\Serializer\Context;
use JMS\SerializerBundle\ContextFactory\ConfiguredContextFactory;

/**
 * Class ConfiguredContextFactoryTest
 */
class ConfiguredContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * testCreateSerializationContext
     *
     * @param string $version
     * @param bool $serializeNulls
     * @param array $attributes
     * @param array $groups
     * @param string $expectedInterface
     * @param string $expectedContextClass
     * @param string $factoryMethod
     *
     * @return void
     * @dataProvider contextConfigDataProvider
     */
    public function testCreateSerializationContext(
        $version,
        $serializeNulls,
        array $attributes,
        array $groups,
        $expectedInterface,
        $expectedContextClass,
        $factoryMethod
    )
    {
        $object = new ConfiguredContextFactory();

        $object->setVersion($version);
        $object->setSerializeNulls($serializeNulls);
        $object->setGroups($groups);
        $object->setAttributes($attributes);

        $this->assertInstanceOf($expectedInterface, $object);

        $context = $object->$factoryMethod();
        /** @var Context $context */
        $this->assertInstanceOf($expectedContextClass, $context);
        $this->assertSame($serializeNulls, $context->shouldSerializeNull());

        $this->assertSame($version, $context->attributes->get('version')->get());
        $this->assertSame($groups, $context->attributes->get('groups')->get());
        foreach ($attributes as $k => $v) {
            $this->assertSame($v, $context->attributes->get($k)->get());
        }
    }

    public function testMaxDepthExclusionStrategy()
    {
        $object = new ConfiguredContextFactory();

        $object->enableMaxDepthChecks();

        $context = $object->createDeserializationContext();
        $this->assertInstanceOf('JMS\Serializer\Exclusion\DepthExclusionStrategy', $context->getExclusionStrategy());

        $context = $object->createDeserializationContext();
        $this->assertInstanceOf('JMS\Serializer\Exclusion\DepthExclusionStrategy', $context->getExclusionStrategy());
    }

    public function contextConfigDataProvider()
    {
        return [
            [
                sprintf('%d.%d.%d', mt_rand(1, 10), mt_rand(1, 10), mt_rand(1, 10)),
                true,
                [
                    'x' => mt_rand(0, PHP_INT_MAX),
                ],
                ['Default', 'Registration'],
                'JMS\Serializer\ContextFactory\SerializationContextFactoryInterface',
                'JMS\Serializer\SerializationContext',
                'createSerializationContext'
            ],
            [
                sprintf('%d.%d.%d', mt_rand(1, 10), mt_rand(1, 10), mt_rand(1, 10)),
                true,
                [
                    'x' => mt_rand(0, PHP_INT_MAX),
                ],
                ['Default', 'Registration'],
                'JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface',
                'JMS\Serializer\DeserializationContext',
                'createDeserializationContext'
            ],
        ];
    }
}
